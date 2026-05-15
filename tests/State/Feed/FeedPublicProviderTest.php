<?php

declare(strict_types=1);

namespace App\Tests\State\Feed;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\State\Pagination\Pagination;
use App\Entity\Post;
use App\Repository\PostRepository;
use App\Service\isLikedEnricher;
use App\State\Feed\FeedPublicProvider;
use PHPUnit\Framework\TestCase;

class FeedPublicProviderTest extends TestCase
{
    public function testReturnsPaginatedAndEnrichedPosts(): void
    {
        $posts = [new Post(), new Post()];
        $operation = new GetCollection();

        $pagination = new Pagination(['items_per_page' => 10]);

        $postRepo = $this->createMock(PostRepository::class);
        $postRepo->expects($this->once())
            ->method('getPaginatedPosts')
            ->with(0, 10)
            ->willReturn($posts)
        ;

        $enricher = $this->createMock(isLikedEnricher::class);
        $enricher->expects($this->once())->method('enrich')->with($posts, Post::class);

        $provider = new FeedPublicProvider($pagination, $postRepo, $enricher);

        $result = $provider->provide($operation);

        $this->assertSame($posts, $result);
    }
}
