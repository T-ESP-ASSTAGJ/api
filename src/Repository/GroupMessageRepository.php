<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\GroupMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GroupMessage>
 */
class GroupMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GroupMessage::class);
    }

    public function findByGroupId(int $groupId, int $limit = 50, int $offset = 0): array
    {
        return $this->createQueryBuilder('gm')
            ->andWhere('gm.groupId = :groupId')
            ->setParameter('groupId', $groupId)
            ->orderBy('gm.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }
}
