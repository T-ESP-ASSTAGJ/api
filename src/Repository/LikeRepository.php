<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Like;
use App\Entity\User;
use DateTimeInterface;
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


    /**
     * * @return array<int, array{
     * likeId: int,
     * entityId: int,
     * entityClass: string,
     * createdAt: DateTimeInterface,
     * userUsername: string
     * }>
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('l')
            ->innerJoin('l.user', 'u')
            ->where('l.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

    }
}