<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Track;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Track>
 */
class TrackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Track::class);
    }

    /** @return Track[] */
    public function searchByQuery(string $query, int $offset, int $limit): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.title LIKE :query OR t.artistName LIKE :query')
            ->setParameter('query', '%'.$query.'%')
            ->orderBy('t.title', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
