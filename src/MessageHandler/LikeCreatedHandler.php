<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\LikeCreatedMessage;
use App\Service\PushNotificationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class LikeCreatedHandler
{
    public function __construct(
        private PushNotificationService $push,
    ) {
    }

    public function __invoke(LikeCreatedMessage $message): void
    {
        $like = $message->like;
        $user = $message->user;
        $owner = $message->owner;
        $content = $message->content;

        $this->push->sendToUser(
            userId: $owner->getId(),
            title: $user->getUsername(),
            body: sprintf('has liked your %s', $like->getEntityClassLabel()),
            data: [
                'postId' => $content->getId(),
                'entityClass' => $like->getEntityClass(),
                'entityId' => (string) $like->getEntityId(),
                'profilePicture' => $user->getProfilePicture(),
                'postImage' => $content->getFrontImage(),
            ],
        );
    }
}
