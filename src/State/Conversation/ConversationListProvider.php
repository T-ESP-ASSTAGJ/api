<?php

declare(strict_types=1);

namespace App\State\Conversation;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Conversation;
use App\Entity\User;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @implements ProviderInterface<Conversation>
 */
final readonly class ConversationListProvider implements ProviderInterface
{
    public function __construct(
        private ConversationRepository $conversationRepository,
        private MessageRepository $messageRepository,
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

        foreach ($conversations as $conversation) {
            $participant = $conversation->getParticipantForUser($user);
            $lastReadAt = $participant?->getLastReadAt();

            // Calculate count of messages created AFTER lastReadAt
            $unreadCount = $this->messageRepository->countUnreadMessages(
                $conversation,
                $lastReadAt
            );

            $conversation->setUnreadCount($unreadCount);
        }

        return $conversations;
    }
}
