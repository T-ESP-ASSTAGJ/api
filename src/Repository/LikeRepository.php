<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Interface\LikeableInterface;
use App\Entity\Like;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Like>
 */
class LikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Like::class);
    }

    public function findContentOwner(Like $like): ?User
    {
        /** @var class-string<LikeableInterface> $entityClass */
        $entityClass = $like->getEntityClass();

        /** @var LikeableInterface|null $resource */
        $resource = $this->getEntityManager()->find(
            $entityClass,
            $like->getEntityId()
        );

        return $resource?->getUser();
    }
}
