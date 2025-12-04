<?php

declare(strict_types=1);

namespace App\State\Conversation;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Conversation;
use App\Entity\User;
use App\Repository\ConversationRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @implements ProviderInterface<Conversation>
 */
final readonly class ConversationListProvider implements ProviderInterface
{
    public function __construct(
        private ConversationRepository $conversationRepository,
        private Security $security,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return array<int, Conversation>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        if (null === $user) {
            throw new UnauthorizedHttpException('Bearer', 'Authentication required');
        }

        $conversations = $this->conversationRepository->findByUser($user);

        // Enrich each conversation with unread count for current user
        foreach ($conversations as $conversation) {
            $conversation->setUnreadCount($conversation->getUnreadCountForUser($user));
        }

        return $conversations;
    }
}
