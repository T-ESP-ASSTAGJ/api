<?php

declare(strict_types=1);

namespace App\Tests\State;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Post;
use App\Service\isLikedEnricher;
use App\State\IsLikedProvider;
use PHPUnit\Framework\TestCase;

class IsLikedProviderTest extends TestCase
{
    /**
     * @var ProviderInterface<object>&\PHPUnit\Framework\MockObject\MockObject
     */
    private ProviderInterface $itemProvider;

    /**
     * @var ProviderInterface<object>&\PHPUnit\Framework\MockObject\MockObject
     */
    private ProviderInterface $collectionProvider;

    /**
     * @var isLikedEnricher&\PHPUnit\Framework\MockObject\MockObject
     */
    private isLikedEnricher $enricher;

    private IsLikedProvider $provider;

    protected function setUp(): void
    {
        $this->itemProvider = $this->createMock(ProviderInterface::class);
        $this->collectionProvider = $this->createMock(ProviderInterface::class);
        $this->enricher = $this->createMock(isLikedEnricher::class);
        $this->provider = new IsLikedProvider(
            $this->itemProvider,
            $this->collectionProvider,
            $this->enricher,
        );
    }

    public function testUsesItemProviderForItemOperation(): void
    {
        $post = new Post();
        $operation = new Get(class: Post::class);

        $this->itemProvider->expects($this->once())->method('provide')->willReturn($post);
        $this->collectionProvider->expects($this->never())->method('provide');
        $this->enricher->expects($this->once())->method('enrich')->with($post, Post::class);

        $result = $this->provider->provide($operation, ['id' => 1]);

        $this->assertSame($post, $result);
    }

    public function testUsesCollectionProviderForCollectionOperation(): void
    {
        $posts = [new Post(), new Post()];
        $operation = new GetCollection(class: Post::class);

        $this->collectionProvider->expects($this->once())->method('provide')->willReturn($posts);
        $this->itemProvider->expects($this->never())->method('provide');
        $this->enricher->expects($this->once())->method('enrich')->with($posts, Post::class);

        $result = $this->provider->provide($operation);

        $this->assertSame($posts, $result);
    }
}
