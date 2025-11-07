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
     * Find all active conversations for a specific user, ordered by last message date.
     *
     * @return Conversation[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.conversationParticipants', 'cp')
            ->leftJoin('c.messages', 'm')
            ->where('cp.user = :user')
            ->andWhere('cp.leftAt IS NULL')
            ->setParameter('user', $user)
            ->groupBy('c.id')
            ->orderBy('MAX(COALESCE(m.createdAt, c.createdAt))', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find a private conversation between two users (both active participants).
     */
    public function findPrivateConversationBetweenUsers(User $user1, User $user2): ?Conversation
    {
        $qb = $this->createQueryBuilder('c');

        return $qb
            ->innerJoin('c.conversationParticipants', 'cp1')
            ->innerJoin('c.conversationParticipants', 'cp2')
            ->where('c.type = :type')
            ->andWhere('cp1.user = :user1')
            ->andWhere('cp1.leftAt IS NULL')
            ->andWhere('cp2.user = :user2')
            ->andWhere('cp2.leftAt IS NULL')
            ->setParameter('type', Conversation::TYPE_PRIVATE)
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Count active conversations for a user.
     */
    public function countByUser(User $user): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(DISTINCT c.id)')
            ->innerJoin('c.conversationParticipants', 'cp')
            ->where('cp.user = :user')
            ->andWhere('cp.leftAt IS NULL')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
