<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::postPersist, priority: 500)]
final readonly class MessageUnreadCountListener
{
    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Message) {
            return;
        }

        $conversation = $entity->getConversation();
        $author = $entity->getAuthor();

        foreach ($conversation->getActiveParticipants() as $participant) {
            if ($participant->getUser()->getId() !== $author->getId()) {
                $participant->incrementUnreadCount();
            }
        }
    }
}
