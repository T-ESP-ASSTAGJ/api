<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Enum\VisibilityEnum;
use App\Message\CommentCreatedMessage;
use App\Repository\FollowRepository;
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
        private FollowRepository $followRepository,
    ) {
    }

    public function __invoke(CommentCreatedMessage $message): void
    {
        $post = $this->postRepository->find($message->postId);
        $user = $this->userRepository->find($message->userId);
        $owner = $post?->getUser();

        if (!$post || !$user || !$owner) {
            return;
        }

        $setting = $owner->getParameters()?->getNotifNewComment() ?? VisibilityEnum::Public;

        if (VisibilityEnum::Private === $setting) {
            return;
        }

        if (VisibilityEnum::Friends === $setting && !$this->followRepository->isMutualFollow($user->getId(), $owner->getId())) {
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
