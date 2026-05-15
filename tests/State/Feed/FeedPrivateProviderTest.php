<?php

declare(strict_types=1);

namespace App\Tests\State\Feed;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\State\Pagination\Pagination;
use App\ApiResource\User\UserFollowOutput;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\FollowRepository;
use App\Repository\PostRepository;
use App\Service\isLikedEnricher;
use App\State\Feed\FeedPrivateProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

class FeedPrivateProviderTest extends TestCase
{
    /**
     * @var Security&\PHPUnit\Framework\MockObject\MockObject
     */
    private Security $security;

    private Pagination $pagination;

    /**
     * @var PostRepository&\PHPUnit\Framework\MockObject\MockObject
     */
    private PostRepository $postRepo;

    /**
     * @var FollowRepository&\PHPUnit\Framework\MockObject\MockObject
     */
    private FollowRepository $followRepo;

    /**
     * @var isLikedEnricher&\PHPUnit\Framework\MockObject\MockObject
     */
    private isLikedEnricher $enricher;

    private FeedPrivateProvider $provider;

    protected function setUp(): void
    {
        $this->security = $this->createMock(Security::class);
        $this->pagination = new Pagination(['items_per_page' => 10]);
        $this->postRepo = $this->createMock(PostRepository::class);
        $this->followRepo = $this->createMock(FollowRepository::class);
        $this->enricher = $this->createMock(isLikedEnricher::class);
        $this->provider = new FeedPrivateProvider(
            $this->security,
            $this->pagination,
            $this->postRepo,
            $this->followRepo,
            $this->enricher,
        );
    }

    public function testThrowsWhenUnauthenticated(): void
    {
        $this->security->method('getUser')->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->provider->provide(new GetCollection());
    }

    public function testReturnsEmptyWhenNoFollowing(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);
        $this->security->method('getUser')->willReturn($user);
        $this->followRepo->method('findFollowing')->willReturn([]);

        $result = $this->provider->provide(new GetCollection());

        $this->assertSame([], $result);
        $this->postRepo->expects($this->never())->method('getFollowingPaginatedPosts');
    }

    public function testReturnsPaginatedPostsForFollowedUsers(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);
        $this->security->method('getUser')->willReturn($user);

        $followOutput = new UserFollowOutput(2, 'bob', null);
        $this->followRepo->method('findFollowing')->willReturn([$followOutput]);

        $posts = [new Post()];
        $this->postRepo->method('getFollowingPaginatedPosts')->with([2], 0, 10)->willReturn($posts);
        $this->enricher->expects($this->once())->method('enrich')->with($posts, Post::class);

        $result = $this->provider->provide(new GetCollection());

        $this->assertSame($posts, $result);
    }
}
