<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /** @return Post[] */
    public function getPaginatedPosts(int $offset, int $limit): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param array<int> $followingUserIds
     *
     * @return Post[]
     */
    public function getFollowingPaginatedPosts(array $followingUserIds, int $offset, int $limit): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->where('p.user IN (:followingUserIds)')
            ->setParameter('followingUserIds', $followingUserIds)
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }
}
