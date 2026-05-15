<?php

declare(strict_types=1);

namespace App\Tests\State\User;

use ApiPlatform\Metadata\GetCollection;
use App\Entity\Like;
use App\Entity\Post;
use App\Entity\User;
use App\Service\isLikedEnricher;
use App\State\User\UserLikedPostProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserLikedPostProviderTest extends TestCase
{
    /**
     * @var EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private EntityManagerInterface $em;

    /**
     * @var isLikedEnricher&\PHPUnit\Framework\MockObject\MockObject
     */
    private isLikedEnricher $enricher;

    private UserLikedPostProvider $provider;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->enricher = $this->createMock(isLikedEnricher::class);
        $this->provider = new UserLikedPostProvider($this->em, $this->enricher);
    }

    public function testReturnsEmptyWhenNoLikes(): void
    {
        $user = new User();
        $userRepo = $this->createMock(EntityRepository::class);
        $userRepo->method('find')->willReturn($user);

        $likeRepo = $this->createMock(EntityRepository::class);
        $likeRepo->method('findBy')->willReturn([]);

        $this->em->method('getRepository')->willReturnMap([
            [User::class, $userRepo],
            [Like::class, $likeRepo],
        ]);

        $result = $this->provider->provide(new GetCollection(), ['id' => 1]);

        $this->assertSame([], $result);
    }

    public function testReturnsPosts(): void
    {
        $user = new User();

        $like = $this->createMock(Like::class);
        $like->method('getEntityId')->willReturn(5);

        $post = new Post();

        $userRepo = $this->createMock(EntityRepository::class);
        $userRepo->method('find')->willReturn($user);

        $likeRepo = $this->createMock(EntityRepository::class);
        $likeRepo->method('findBy')->willReturn([$like]);

        $postRepo = $this->createMock(EntityRepository::class);
        $postRepo->method('findBy')->willReturn([$post]);

        $this->em->method('getRepository')->willReturnMap([
            [User::class, $userRepo],
            [Like::class, $likeRepo],
            [Post::class, $postRepo],
        ]);

        $this->enricher->expects($this->once())->method('enrich');

        $result = $this->provider->provide(new GetCollection(), ['id' => 1]);

        $this->assertContains($post, $result);
    }

    public function testThrowsWhenUserNotFound(): void
    {
        $userRepo = $this->createMock(EntityRepository::class);
        $userRepo->method('find')->willReturn(null);
        $this->em->method('getRepository')->willReturn($userRepo);

        $this->expectException(NotFoundHttpException::class);
        $this->provider->provide(new GetCollection(), ['id' => 999]);
    }
}
