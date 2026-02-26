<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Post;
use App\Message\PersistPostViewsMessage;
use App\Service\Post\PostViewsPersistenceServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class PersistPostViewsMessageHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PostViewsPersistenceServiceInterface $postViewsPersistenceService,
    ) {
    }

    public function __invoke(PersistPostViewsMessage $message): void
    {
        $postId = $message->getPostId();
        $post = $this->entityManager->find(Post::class, $postId);

        if (null === $post) {
            return;
        }

        $this->postViewsPersistenceService->persistViews($post->getId());
    }
}
