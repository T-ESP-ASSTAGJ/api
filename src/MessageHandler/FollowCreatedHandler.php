<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\FollowCreatedMessage;
use App\Repository\UserRepository;
use App\Service\PushNotificationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class FollowCreatedHandler
{
    public function __construct(
        private PushNotificationService $push,
        private UserRepository $userRepository,
    ) {
    }

    public function __invoke(FollowCreatedMessage $message): void
    {
        $userToFollow = $this->userRepository->find($message->userToFollowId);
        $currentUser = $this->userRepository->find($message->currentUserId);

        if (!$userToFollow || !$currentUser) {
            return;
        }

        $setting = $userToFollow->getParameters()?->getNotifNewFollower() ?? true;

        if (false === $setting) {
            return;
        }

        $this->push->sendToUser(
            userId: $userToFollow->getId(),
            title: $currentUser->getUsername(),
            body: 'just followed you',
            data: [
                'userId' => $currentUser->getId(),
                'profilePicture' => $currentUser->getProfilePicture(),
            ],
        );
    }
}
