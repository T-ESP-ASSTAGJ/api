<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Interface\LikeableInterface;
use App\Entity\Like;
use App\Entity\Post;
use App\Entity\User;
use App\Service\isLikedEnricher;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

class isLikedEnricherTest extends TestCase
{
    private Security&\PHPUnit\Framework\MockObject\MockObject $security;

    private EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject $em;

    private isLikedEnricher $service;

    protected function setUp(): void
    {
        $this->security = $this->createMock(Security::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->service = new isLikedEnricher($this->security, $this->em);
    }

    public function testEnrichDoesNothingWhenNoUser(): void
    {
        $this->security->method('getUser')->willReturn(null);
        $this->em->expects($this->never())->method('createQueryBuilder');

        $this->service->enrich(new Post(), Post::class);
    }

    public function testEnrichDoesNothingWhenResourceNotLikeable(): void
    {
        $this->security->method('getUser')->willReturn(new User());
        $this->em->expects($this->never())->method('createQueryBuilder');

        $this->service->enrich(new User(), User::class);
    }

    public function testEnrichDoesNothingWhenEnumNotFound(): void
    {
        $this->security->method('getUser')->willReturn(new User());
        $this->em->expects($this->never())->method('createQueryBuilder');

        $this->service->enrich(new FakeLikeableEntity(), FakeLikeableEntity::class);
    }

    public function testEnrichHandlesEmptyCollection(): void
    {
        $this->security->method('getUser')->willReturn(new User());
        $this->em->expects($this->never())->method('createQueryBuilder');

        $this->service->enrich([], Post::class);
    }

    public function testEnrichHandlesCollectionWithNullIds(): void
    {
        $this->security->method('getUser')->willReturn(new User());
        // Posts with null IDs are skipped, so QueryBuilder is never called
        $this->em->expects($this->never())->method('createQueryBuilder');

        $this->service->enrich([new Post()], Post::class);
    }

    public function testEnrichHandlesCollectionSetsIsLikedForMatchedItems(): void
    {
        $user = new User();
        $this->security->method('getUser')->willReturn($user);

        $post = new Post();
        $ref = new \ReflectionProperty(Post::class, 'id');
        $ref->setValue($post, 42);

        $query = $this->createMock(Query::class);
        $query->method('getSingleColumnResult')->willReturn([42]);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('select')->willReturnSelf();
        $qb->method('from')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('andWhere')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);

        $this->em->method('createQueryBuilder')->willReturn($qb);

        $this->service->enrich([$post], Post::class);

        $this->assertTrue($post->getIsLiked());
    }

    public function testEnrichHandlesCollectionDoesNotSetIsLikedForUnmatchedItems(): void
    {
        $user = new User();
        $this->security->method('getUser')->willReturn($user);

        $post = new Post();
        $ref = new \ReflectionProperty(Post::class, 'id');
        $ref->setValue($post, 99);

        $query = $this->createMock(Query::class);
        $query->method('getSingleColumnResult')->willReturn([]);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('select')->willReturnSelf();
        $qb->method('from')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('andWhere')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);

        $this->em->method('createQueryBuilder')->willReturn($qb);

        $this->service->enrich([$post], Post::class);

        $this->assertFalse($post->getIsLiked());
    }

    public function testEnrichHandlesSingleItemLiked(): void
    {
        $user = new User();
        $this->security->method('getUser')->willReturn($user);

        $post = new Post();

        /** @var EntityRepository<Like>&\PHPUnit\Framework\MockObject\MockObject $repo */
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('count')->willReturn(1);

        $this->em->method('getRepository')->with(Like::class)->willReturn($repo);

        $this->service->enrich($post, Post::class);

        $this->assertTrue($post->getIsLiked());
    }

    public function testEnrichHandlesSingleItemNotLiked(): void
    {
        $user = new User();
        $this->security->method('getUser')->willReturn($user);

        $post = new Post();

        /** @var EntityRepository<Like>&\PHPUnit\Framework\MockObject\MockObject $repo */
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('count')->willReturn(0);

        $this->em->method('getRepository')->with(Like::class)->willReturn($repo);

        $this->service->enrich($post, Post::class);

        $this->assertFalse($post->getIsLiked());
    }
}

class FakeLikeableEntity implements LikeableInterface
{
    public function getId(): ?int
    {
        return 1;
    }

    public function getUser(): User
    {
        return new User();
    }

    public function getLikesCount(): int
    {
        return 0;
    }

    public function setLikesCount(int $likesCount): void
    {
    }

    public function getIsLiked(): bool
    {
        return false;
    }

    public function setIsLiked(bool $isLiked): void
    {
    }
}
