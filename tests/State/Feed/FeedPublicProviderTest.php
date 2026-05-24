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
use App\State\Feed\FeedPublicProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

class FeedPublicProviderTest extends TestCase
{
    /**
     * @var PostRepository&MockObject
     */
    private PostRepository $postRepo;

    /**
     * @var isLikedEnricher&MockObject
     */
    private isLikedEnricher $enricher;

    /**
     * @var Security&MockObject
     */
    private Security $security;

    /**
     * @var FollowRepository&MockObject
     */
    private FollowRepository $followRepo;

    private Pagination $pagination;

    private FeedPublicProvider $provider;

    /**
     * @var array{likedTrackIds: int[], likedArtistNames: string[], commentedTrackIds: int[], commentedArtistNames: string[]}
     */
    private array $emptyProfile = [
        'likedTrackIds' => [],
        'likedArtistNames' => [],
        'commentedTrackIds' => [],
        'commentedArtistNames' => [],
    ];

    protected function setUp(): void
    {
        $this->postRepo = $this->createMock(PostRepository::class);
        $this->enricher = $this->createMock(isLikedEnricher::class);
        $this->security = $this->createMock(Security::class);
        $this->followRepo = $this->createMock(FollowRepository::class);
        $this->pagination = new Pagination(['items_per_page' => 10]);
        $this->provider = new FeedPublicProvider(
            $this->pagination,
            $this->postRepo,
            $this->enricher,
            $this->security,
            $this->followRepo,
        );
    }

    public function testUnauthenticatedUserGetsPaginatedPosts(): void
    {
        $posts = [new Post(), new Post()];
        $this->security->method('getUser')->willReturn(null);

        $this->postRepo->expects($this->once())
            ->method('getPaginatedPosts')->with(0, 10)->willReturn($posts)
        ;
        $this->postRepo->expects($this->never())->method('getScoredPaginatedPosts');
        $this->followRepo->expects($this->never())->method('findFollowing');

        $this->enricher->expects($this->once())->method('enrich')->with($posts, Post::class);

        $result = $this->provider->provide(new GetCollection());
        $this->assertSame($posts, $result);
    }

    public function testAuthenticatedUserWithNoInteractionsOrFollowsGetsScoredPosts(): void
    {
        $posts = [new Post()];
        $user = $this->makeUser(1);

        $this->security->method('getUser')->willReturn($user);
        $this->postRepo->method('getUserInteractionProfile')->with(1)->willReturn($this->emptyProfile);
        $this->followRepo->method('findFollowing')->with(1)->willReturn([]);

        $this->postRepo->expects($this->once())
            ->method('getScoredPaginatedPosts')
            ->with($this->emptyProfile, [], 0, 10)
            ->willReturn($posts)
        ;
        $this->postRepo->expects($this->never())->method('getPaginatedPosts');

        $result = $this->provider->provide(new GetCollection());
        $this->assertSame($posts, $result);
    }

    public function testAuthenticatedUserWithProfileAndFollowsGetsScoredPosts(): void
    {
        $posts = [new Post(), new Post()];
        $user = $this->makeUser(1);
        $profile = [
            'likedTrackIds' => [5],
            'likedArtistNames' => ['Queen'],
            'commentedTrackIds' => [6],
            'commentedArtistNames' => ['Bowie'],
        ];
        $followOutput = new UserFollowOutput(42, 'friend', null);

        $this->security->method('getUser')->willReturn($user);
        $this->postRepo->method('getUserInteractionProfile')->with(1)->willReturn($profile);
        $this->followRepo->method('findFollowing')->with(1)->willReturn([$followOutput]);

        $this->postRepo->expects($this->once())
            ->method('getScoredPaginatedPosts')
            ->with($profile, [42], 0, 10)
            ->willReturn($posts)
        ;

        $this->enricher->expects($this->once())->method('enrich')->with($posts, Post::class);

        $result = $this->provider->provide(new GetCollection());
        $this->assertSame($posts, $result);
    }

    private function makeUser(int $id): User
    {
        $user = new User();
        $ref = new \ReflectionProperty(User::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($user, $id);

        return $user;
    }
}
