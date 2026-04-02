<?php

declare(strict_types=1);

namespace App\Repository;

/**
 * Trait for building search queries with pagination and filtering.
 */
trait SearchQueryTrait
{
    /**
     * Builds a search query with pagination and ordering.
     *
     * @param string $searchField Field to search on
     * @param string $query Search query term
     * @param int $offset Pagination offset
     * @param int $limit Pagination limit
     * @param string $orderField Field to order by
     * @param string $direction Sort direction
     * @return \Doctrine\ORM\Query<mixed>
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
}
