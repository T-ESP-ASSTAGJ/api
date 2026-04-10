<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function countUnreadMessages(Conversation $conversation, ?\DateTimeImmutable $lastReadAt): int
    {
        dump($conversation, $lastReadAt);
        $qb = $this->createQueryBuilder('m')
            ->select('count(m.id)')
            ->where('m.conversation = :conversation')
            ->setParameter('conversation', $conversation);

        if ($lastReadAt) {
            $qb->andWhere('m.createdAt > :lastReadAt')
                ->setParameter('lastReadAt', $lastReadAt);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
