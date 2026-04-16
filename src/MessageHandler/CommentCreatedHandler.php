<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\CommentCreatedMessage;
use App\Service\PushNotificationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CommentCreatedHandler
{
    public function __construct(
        private PushNotificationService $push,
    ) {
    }

    public function __invoke(CommentCreatedMessage $message): void
    {
        $post = $message->post;
        $user = $message->user;

        $this->push->sendToUser(
            userId: $post->getUser()->getId(),
            title: $user->getUsername(),
            body: 'just commented your post',
            data: [
                'userId' => $user->getId(),
                'profilePicture' => $user->getProfilePicture(),
            ]
        );
    }
}
