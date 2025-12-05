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
        $conversations = $this->createQueryBuilder('c')
            ->innerJoin('c.participants', 'p')
            ->where('p.user = :user')
            ->andWhere('p.leftAt IS NULL')
            ->setParameter('user', $user)
            ->orderBy('c.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();

        // Set unread count from the participant
        foreach ($conversations as $conversation) {
            foreach ($conversation->getParticipants() as $participant) {
                if ($participant->getUser()->getId() === $user->getId() && null === $participant->getLeftAt()) {
                    $conversation->setUnreadCount($participant->getUnreadCount());
                    break;
                }
            }
        }

        return $conversations;
    }
}
