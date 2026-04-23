<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Artist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Artist>
 */
class ArtistRepository extends ServiceEntityRepository
{
    use SearchQueryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Artist::class);
    }

    /** @return Artist[] */
    public function searchByQuery(string $query, int $offset, int $limit): array
    {
        return $this->buildSearchQuery('name', $query, $offset, $limit, 'name', 'ASC')->getResult();
    }
}
