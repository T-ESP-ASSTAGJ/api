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
     * Recherche les conversations où l'utilisateur est un participant actif.
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
            ->getOneOrNullResult()
        ;
    }

    /**
     * Recherche les conversations où l'utilisateur est un participant actif.
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
            ->getResult()
        ;
    }
}
