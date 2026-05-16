<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Post;
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

    public function testUpdateViewsCountSetsCorrectValue(): void
    {
        $post = PostFactory::createOne();
        $postId = $post->getId();

        $this->repo->updateViewsCount($postId, 42);

        $this->em->clear();
        $updated = $this->em->find(Post::class, $postId);

        $this->assertSame(42, $updated->getViewsCount());
    }
}
