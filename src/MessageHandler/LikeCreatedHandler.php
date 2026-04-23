<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\LikeCreatedMessage;
use App\Repository\LikeRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\PushNotificationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class LikeCreatedHandler
{
    public function __construct(
        private PushNotificationService $push,
        private UserRepository $userRepository,
        private LikeRepository $likeRepository,
        private PostRepository $postRepository,
    ) {
    }

    public function __invoke(LikeCreatedMessage $message): void
    {
        $user = $this->userRepository->find($message->userId);
        $owner = $this->userRepository->find($message->ownerId);
        $like = $this->likeRepository->find($message->likeId);
        $content = $this->postRepository->find($message->postId);

        if (!$user || !$owner || !$like || !$content) {
            return;
        }

        $this->push->sendToUser(
            userId: $owner->getId(),
            title: $user->getUsername(),
            body: \sprintf('has liked your %s', $like->getEntityClassLabel()),
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
