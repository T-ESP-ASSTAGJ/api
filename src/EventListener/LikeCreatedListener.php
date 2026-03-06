<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Like;
use App\Repository\LikeRepository;
use App\Service\PushNotificationService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postPersist, entity: Like::class)]
class LikeCreatedListener
{
    public function __construct(
        private PushNotificationService $push,
        private LikeRepository $likeRepository,
    ) {
    }

    public function postPersist(Like $like, PostPersistEventArgs $args): void
    {
        $liker = $like->getUser();
        $recipient = $this->likeRepository->findContentOwner($like);

        if (null === $recipient) {
            return;
        }

        if ($recipient->getId() === $liker->getId()) {
            return;
        }

        $entityType = strtolower((new \ReflectionClass($like->getEntityClass()))->getShortName());
        $this->push->sendToUser(
            userId: $recipient->getId(),
            title: 'New like',
            body: sprintf('%s liked your %s', $liker->getUsername(), $entityType),
            data: [
                'type' => 'like',
                'entity_class' => $like->getEntityClass(),
                'entity_id' => (string) $like->getEntityId(),
            ]
        );
        // TODO: add mercure update here
    }
}
