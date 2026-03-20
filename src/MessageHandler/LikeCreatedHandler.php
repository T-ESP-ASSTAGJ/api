<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\LikeCreatedMessage;
use App\Repository\LikeRepository;
use App\Service\PushNotificationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class LikeCreatedHandler
{
    public function __construct(
        private LikeRepository $likeRepository,
        private PushNotificationService $push,
    ) {}

    public function __invoke(LikeCreatedMessage $message): void
    {
        $like = $this->likeRepository->find($message->likeId);
        if (null === $like) {
            return;
        }

        $liker = $like->getUser();
        $recipient = $this->likeRepository->findContentOwner($like);

        if (null === $recipient || $recipient->getId() === $liker->getId()) {
            return;
        }

        $this->push->sendToUser(
            userId: $recipient->getId(),
            title: $liker->getUsername(),
            body: sprintf('has liked your %s', $like->getEntityClass()),
            data: [
                'entity_class' => $like->getEntityClass(),
                'entity_id' => (string) $like->getEntityId(),
            ]
        );
    }
}
