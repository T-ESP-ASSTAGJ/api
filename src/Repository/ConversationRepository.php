<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
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
     * Find conversations where the user is an active participant.
     */
    public function findPrivateConversation(User $userA, User $userB): ?Conversation
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.participants', 'p1')
            ->innerJoin('c.participants', 'p2')
            ->where('c.isGroup = :isGroup')
            ->andWhere('p1.user = :userA')
            ->andWhere('p2.user = :userB')
            ->setParameter('isGroup', false)
            ->setParameter(':userA', $userA)
            ->setParameter(':userB', $userB)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
       }

    /**
     * Find conversations where the user is an active participant.
     *
     * @return array<Conversation>
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.participants', 'p')
            ->where('p.user = :user')
            ->andWhere('p.leftAt IS NULL')
            ->setParameter('user', $user)
            ->orderBy('c.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find conversations with unread count for a specific user.
     *
     * @return array<Conversation>
     */
    public function findByUserWithUnreadCount(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->select('c', 'p')
            ->innerJoin('c.participants', 'p', 'WITH', 'p.user = :user')
            ->where('p.leftAt IS NULL')
            ->setParameter('user', $user)
            ->orderBy('c.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
