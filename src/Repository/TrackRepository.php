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
    use SearchQueryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Track::class);
    }

    /** @return Track[] */
    public function searchByQuery(string $query, int $offset, int $limit): array
    {
        return $this->buildMultiFieldSearchQuery(['title', 'artistName'], $query, $offset, $limit, 'title', 'ASC')->getResult();
    }
}
