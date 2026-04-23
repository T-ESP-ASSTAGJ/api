<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\CommentCreatedMessage;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\PushNotificationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CommentCreatedHandler
{
    public function __construct(
        private PushNotificationService $push,
        private PostRepository $postRepository,
        private UserRepository $userRepository,
    ) {
    }

    public function __invoke(CommentCreatedMessage $message): void
    {
        $post = $this->postRepository->find($message->postId);
        $user = $this->userRepository->find($message->userId);

        if (!$post || !$user) {
            return;
        }

        $this->push->sendToUser(
            userId: $post->getUser()->getId(),
            title: $user->getUsername(),
            body: 'just commented your post',
            data: [
                'userId' => $user->getId(),
                'profilePicture' => $user->getProfilePicture(),
            ],
        );
    }
}
