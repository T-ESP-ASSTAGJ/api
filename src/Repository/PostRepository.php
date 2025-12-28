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
     * @param array<int> $followedUserIds
     *
     * @return Post[]
     */
    public function getFollowedPaginatedPosts(array $followedUserIds, int $offset, int $limit): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->where('p.user IN (:followedUserIds)')
            ->setParameter('followedUserIds', $followedUserIds)
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }
}
