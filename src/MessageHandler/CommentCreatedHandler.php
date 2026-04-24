<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\CommentCreatedMessage;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\PushNotificationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class CommentCreatedHandler
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
        $owner = $post?->getUser();

        if (!$post
            || !$user
            || !$owner->getParameters()?->getNotifNewComment()
        ) {
            return;
        }

        $this->push->sendToUser(
            userId: $owner->getId(),
            title: $user->getUsername(),
            body: 'just commented your post',
            data: [
                'userId' => $user->getId(),
                'profilePicture' => $user->getProfilePicture(),
            ],
        );
    }
}
