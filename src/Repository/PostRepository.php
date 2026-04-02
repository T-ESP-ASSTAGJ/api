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

    /**
     * @return Post[]
     */
    public function getPaginatedPosts(int $offset, int $limit): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

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
            ->setMaxResults($limit)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /** @return Post[] */
    public function searchByQuery(string $query, int $offset, int $limit): array
    {
        return $this->buildSearchQuery('caption', $query, $offset, $limit, 'createdAt', 'DESC')->getResult();
    }

    /**
     * @param string $searchField Field to search on
     * @param string $query Search query term
     * @param int $offset Pagination offset
     * @param int $limit Pagination limit
     * @param string $orderField Field to order by
     * @param string $direction Sort direction
     */
    protected function buildSearchQuery(string $searchField, string $query, int $offset, int $limit, string $orderField, string $direction): \Doctrine\ORM\Query
    {
        return $this->createQueryBuilder('e')
            ->where('e.'.$searchField.' LIKE :query')
            ->setParameter('query', '%'.$query.'%')
            ->orderBy('e.'.$orderField, $direction)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery();
    }

    public function updateViewsCount(int $postId, int $count): void
    {
        $this->createQueryBuilder('p')
            ->update()
            ->set('p.viewsCount', ':count')
            ->where('p.id = :id')
            ->setParameter('count', $count)
            ->setParameter('id', $postId)
            ->getQuery()
            ->execute()
        ;
    }
}
