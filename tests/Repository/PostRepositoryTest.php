<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Enum\LikeableTypeEnum;
use App\Entity\Like;
use App\Entity\Post;
use App\Factory\CommentFactory;
use App\Factory\PostFactory;
use App\Factory\TrackFactory;
use App\Factory\UserFactory;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class PostRepositoryTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    private EntityManagerInterface $em;

    private PostRepository $repo;

    protected function setUp(): void
    {
        $container = static::getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->repo = $container->get(PostRepository::class);
    }

    public function testGetPaginatedPostsReturnsPosts(): void
    {
        PostFactory::createMany(3);

        $results = $this->repo->getPaginatedPosts(0, 10);

        $this->assertCount(3, $results);
        $this->assertContainsOnlyInstancesOf(Post::class, $results);
    }

    public function testGetPaginatedPostsRespectsOffsetAndLimit(): void
    {
        PostFactory::createMany(5);

        $this->assertCount(2, $this->repo->getPaginatedPosts(0, 2));
        $this->assertCount(2, $this->repo->getPaginatedPosts(2, 2));
        $this->assertCount(1, $this->repo->getPaginatedPosts(4, 10));
    }

    public function testGetFollowingPaginatedPostsFiltersByUserIds(): void
    {
        $followed = UserFactory::createOne();
        $other = UserFactory::createOne();

        PostFactory::createOne(['user' => $followed]);
        PostFactory::createOne(['user' => $other]);

        $results = $this->repo->getFollowingPaginatedPosts([$followed->getId()], 0, 10);

        $this->assertCount(1, $results);
        $this->assertSame($followed->getId(), $results[0]->getUser()->getId());
    }

    public function testFindByCaptionReturnsMatchingPosts(): void
    {
        PostFactory::createOne(['caption' => 'Hello unique caption world']);
        PostFactory::createOne(['caption' => 'Completely different text']);

        $results = $this->repo->findByCaption('unique caption', 0, 10);

        $this->assertCount(1, $results);
        $this->assertStringContainsString('unique caption', $results[0]->getCaption());
    }

    public function testSearchByTrackTitleReturnsMatchingPosts(): void
    {
        $track = TrackFactory::createOne(['title' => 'Bohemian Rhapsody Special']);
        PostFactory::createOne(['track' => $track]);
        PostFactory::createOne();

        $results = $this->repo->searchByTrackTitle('Bohemian Rhapsody', 0, 10);

        $this->assertCount(1, $results);
        $this->assertStringContainsString('Bohemian Rhapsody', $results[0]->getTrack()->getTitle());
    }

    public function testSearchByArtistNameReturnsMatchingPosts(): void
    {
        $track = TrackFactory::createOne(['artistName' => 'Queen Unique Artist']);
        PostFactory::createOne(['track' => $track]);
        PostFactory::createOne();

        $results = $this->repo->searchByArtistName('Queen Unique', 0, 10);

        $this->assertCount(1, $results);
        $this->assertStringContainsString('Queen Unique', $results[0]->getTrack()->getArtistName());
    }

    public function testGetUserInteractionProfileReturnsEmptyArraysWhenNoInteractions(): void
    {
        $user = UserFactory::createOne();
        PostFactory::createMany(2);

        $profile = $this->repo->getUserInteractionProfile($user->getId());

        $this->assertSame([], $profile['likedTrackIds']);
        $this->assertSame([], $profile['likedArtistNames']);
        $this->assertSame([], $profile['commentedTrackIds']);
        $this->assertSame([], $profile['commentedArtistNames']);
    }

    public function testGetUserInteractionProfileSeparatesLikedFromCommented(): void
    {
        $user = UserFactory::createOne();
        $likedTrack = TrackFactory::createOne(['artistName' => 'Pink Floyd']);
        $commentedTrack = TrackFactory::createOne(['artistName' => 'David Bowie']);
        $likedPost = PostFactory::createOne(['track' => $likedTrack]);
        $commentedPost = PostFactory::createOne(['track' => $commentedTrack]);

        $like = new Like();
        $like->setUser($user);
        $like->setEntityId($likedPost->getId());
        $like->setEntityClass(LikeableTypeEnum::Post);
        $this->em->persist($like);
        CommentFactory::createOne(['user' => $user, 'post' => $commentedPost]);
        $this->em->flush();

        $profile = $this->repo->getUserInteractionProfile($user->getId());

        $this->assertContains($likedTrack->getId(), $profile['likedTrackIds']);
        $this->assertContains('Pink Floyd', $profile['likedArtistNames']);
        $this->assertNotContains($likedTrack->getId(), $profile['commentedTrackIds']);

        $this->assertContains($commentedTrack->getId(), $profile['commentedTrackIds']);
        $this->assertContains('David Bowie', $profile['commentedArtistNames']);
        $this->assertNotContains($commentedTrack->getId(), $profile['likedTrackIds']);
    }

    public function testGetUserInteractionProfileDeduplicatesWithinEachSet(): void
    {
        $user = UserFactory::createOne();
        $track = TrackFactory::createOne(['artistName' => 'Beatles']);
        $post1 = PostFactory::createOne(['track' => $track]);
        $post2 = PostFactory::createOne(['track' => $track]);

        foreach ([$post1->getId(), $post2->getId()] as $postId) {
            $like = new Like();
            $like->setUser($user);
            $like->setEntityId($postId);
            $like->setEntityClass(LikeableTypeEnum::Post);
            $this->em->persist($like);
        }
        $this->em->flush();

        $profile = $this->repo->getUserInteractionProfile($user->getId());

        $this->assertCount(1, array_filter($profile['likedTrackIds'], fn ($id) => $id === $track->getId()));
        $this->assertCount(1, array_filter($profile['likedArtistNames'], fn ($name) => 'Beatles' === $name));
    }

    public function testGetScoredPaginatedPostsRanksLikedTrackFirst(): void
    {
        $likedTrack = TrackFactory::createOne(['artistName' => 'Artist A']);
        $otherTrack = TrackFactory::createOne(['artistName' => 'Artist B']);

        $matchingPost = PostFactory::createOne(['track' => $likedTrack]);
        PostFactory::createMany(2, ['track' => $otherTrack]);

        $profile = array_merge($this->emptyProfile(), ['likedTrackIds' => [$likedTrack->getId()]]);
        $results = $this->repo->getScoredPaginatedPosts($profile, [], 0, 10);

        $this->assertCount(3, $results);
        $this->assertSame($matchingPost->getId(), $results[0]->getId());
    }

    public function testGetScoredPaginatedPostsCommentedTrackScoresHigherThanLiked(): void
    {
        $track = TrackFactory::createOne(['artistName' => 'Shared Artist']);
        $otherTrack = TrackFactory::createOne(['artistName' => 'Other Artist']);

        $commentedPost = PostFactory::createOne(['track' => $track]);
        $likedPost = PostFactory::createOne(['track' => $otherTrack]);

        $profile = array_merge($this->emptyProfile(), [
            'commentedTrackIds' => [$track->getId()],  // +15
            'likedTrackIds' => [$otherTrack->getId()],  // +10
        ]);
        $results = $this->repo->getScoredPaginatedPosts($profile, [], 0, 10);

        $this->assertSame($commentedPost->getId(), $results[0]->getId());
        $this->assertSame($likedPost->getId(), $results[1]->getId());
    }

    public function testGetScoredPaginatedPostsRanksMatchingArtistAboveUnrelated(): void
    {
        $artistTrack = TrackFactory::createOne(['artistName' => 'Known Artist']);
        $unknownTrack = TrackFactory::createOne(['artistName' => 'Unknown Artist']);

        $artistPost = PostFactory::createOne(['track' => $artistTrack]);
        PostFactory::createOne(['track' => $unknownTrack]);

        $profile = array_merge($this->emptyProfile(), ['likedArtistNames' => ['Known Artist']]);
        $results = $this->repo->getScoredPaginatedPosts($profile, [], 0, 10);

        $this->assertCount(2, $results);
        $this->assertSame($artistPost->getId(), $results[0]->getId());
    }

    public function testGetScoredPaginatedPostsRanksCommentedArtistAboveUnrelated(): void
    {
        $commentedArtistTrack = TrackFactory::createOne(['artistName' => 'Commented Artist']);
        $otherTrack = TrackFactory::createOne(['artistName' => 'Other Artist']);

        $commentedArtistPost = PostFactory::createOne(['track' => $commentedArtistTrack]);
        PostFactory::createOne(['track' => $otherTrack]);

        $profile = array_merge($this->emptyProfile(), ['commentedArtistNames' => ['Commented Artist']]);
        $results = $this->repo->getScoredPaginatedPosts($profile, [], 0, 10);

        $this->assertCount(2, $results);
        $this->assertSame($commentedArtistPost->getId(), $results[0]->getId());
    }

    public function testGetScoredPaginatedPostsBoostsFollowedUserPosts(): void
    {
        $followedUser = UserFactory::createOne();
        $otherUser = UserFactory::createOne();
        $track = TrackFactory::createOne();

        $followedPost = PostFactory::createOne(['user' => $followedUser, 'track' => $track]);
        PostFactory::createOne(['user' => $otherUser, 'track' => $track]);

        $results = $this->repo->getScoredPaginatedPosts($this->emptyProfile(), [$followedUser->getId()], 0, 10);

        $this->assertCount(2, $results);
        $this->assertSame($followedPost->getId(), $results[0]->getId());
    }

    public function testUpdateViewsCountSetsCorrectValue(): void
    {
        $post = PostFactory::createOne();
        $postId = $post->getId();

        $this->repo->updateViewsCount($postId, 42);

        $this->em->clear();
        $updated = $this->em->find(Post::class, $postId);

        $this->assertSame(42, $updated->getViewsCount());
    }

    /**
     * @return array{likedTrackIds: int[], likedArtistNames: string[], commentedTrackIds: int[], commentedArtistNames: string[]}
     */
    private function emptyProfile(): array
    {
        return [
            'likedTrackIds' => [],
            'likedArtistNames' => [],
            'commentedTrackIds' => [],
            'commentedArtistNames' => [],
        ];
    }
}
