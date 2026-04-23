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
    use SearchQueryTrait;
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

    /** @return Post[] */
    public function searchByTrackTitle(string $query, int $offset, int $limit): array
    {
        return $this->searchByTrackField('title', $query, $offset, $limit);
    }

    /** @return Post[] */
    public function searchByArtistName(string $query, int $offset, int $limit): array
    {
        return $this->searchByTrackField('artistName', $query, $offset, $limit);
    }

    /** @return Post[] */
    private function searchByTrackField(string $field, string $query, int $offset, int $limit): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.track', 't')
            ->where('t.'.$field.' LIKE :query')
            ->setParameter('query', '%'.$query.'%')
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
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
