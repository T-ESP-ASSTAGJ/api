<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\FollowCreatedMessage;
use App\Service\PushNotificationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class FollowCreatedHandler
{
    public function __construct(
        private PushNotificationService $push,
    ) {
    }

    public function __invoke(FollowCreatedMessage $message): void
    {
        $userToFollow = $message->userToFollow;
        $currentUser = $message->currentUser;

        $this->push->sendToUser(
            userId: $userToFollow->getId(),
            title: $currentUser->getUsername(),
            body: 'just followed you! 🤤',
            data: [
                'userId' => $currentUser->getId(),
                'profilePicture' => $currentUser->getProfilePicture(),
            ]
        );
    }
}
