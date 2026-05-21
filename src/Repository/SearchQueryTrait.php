<?php

declare(strict_types=1);

namespace App\Repository;

/**
 * Trait pour la construction de requêtes de recherche avec pagination et filtrage.
 */
trait SearchQueryTrait
{
    /**
     * Construit une requête de recherche avec pagination et tri.
     *
     * @param string $searchField Champ sur lequel effectuer la recherche
     * @param string $query Terme de la requête de recherche
     * @param int $offset Décalage de pagination
     * @param int $limit Limite de pagination
     * @param string $orderField Champ de tri
     * @param string $direction Sens du tri
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
            ->getQuery()
        ;
    }
}
