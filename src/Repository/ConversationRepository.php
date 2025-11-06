<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conversation>
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    /**
     * Find all conversations for a specific user, ordered by last message date.
     *
     * @return Conversation[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.participants', 'p')
            ->leftJoin('c.messages', 'm')
            ->where('p.id = :userId')
            ->setParameter('userId', $user->getId())
            ->groupBy('c.id')
            ->orderBy('MAX(COALESCE(m.createdAt, c.createdAt))', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find a private conversation between two users.
     */
    public function findPrivateConversationBetweenUsers(User $user1, User $user2): ?Conversation
    {
        $qb = $this->createQueryBuilder('c');

        return $qb
            ->innerJoin('c.participants', 'p1')
            ->innerJoin('c.participants', 'p2')
            ->where('c.type = :type')
            ->andWhere('p1.id = :user1Id')
            ->andWhere('p2.id = :user2Id')
            ->andWhere(
                $qb->expr()->eq(
                    $qb->expr()->count('c.participants'),
                    ':participantCount'
                )
            )
            ->setParameter('type', Conversation::TYPE_PRIVATE)
            ->setParameter('user1Id', $user1->getId())
            ->setParameter('user2Id', $user2->getId())
            ->setParameter('participantCount', 2)
            ->groupBy('c.id')
            ->having($qb->expr()->eq($qb->expr()->count('c.participants'), ':participantCount'))
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Count conversations for a user.
     */
    public function countByUser(User $user): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->innerJoin('c.participants', 'p')
            ->where('p.id = :userId')
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }
}
