<?php

declare(strict_types=1);

namespace App\State\Conversation;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Conversation\ConversationDetailOutput;
use App\Entity\Conversation;
use App\Entity\User;
use App\Repository\ConversationRepository;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProviderInterface<ConversationDetailOutput>
 */
final readonly class ConversationGetProvider implements ProviderInterface
{
    public function __construct(
        private Security $security,
        private ConversationRepository $conversationRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ConversationDetailOutput
    {
        /** @var User|null $currentUser */
        $currentUser = $this->security->getUser();
        if (!$currentUser instanceof User) {
            throw new \RuntimeException('User must be authenticated');
        }

        $conversationId = $uriVariables['id'] ?? null;
        if (!$conversationId) {
            throw new \InvalidArgumentException('Conversation ID is required');
        }

        /** @var Conversation|null $conversation */
        $conversation = $this->conversationRepository->find($conversationId);
        if (!$conversation) {
            throw new \RuntimeException('Conversation not found');
        }

        // Check if user is participant
        if (!$conversation->isUserParticipant($currentUser)) {
            throw new \RuntimeException('You are not a participant of this conversation');
        }

        return $this->mapToOutput($conversation);
    }

    private function mapToOutput(Conversation $conversation): ConversationDetailOutput
    {
        $participants = [];
        foreach ($conversation->getConversationParticipants() as $cp) {
            $participants[] = [
                'user_id' => $cp->getUser()->getId(),
                'username' => $cp->getUser()->getUsername(),
                'profile_picture' => $cp->getUser()->getProfilePicture(),
                'joined_at' => $cp->getJoinedAt()->format('c'),
                'left_at' => $cp->getLeftAt()?->format('c'),
            ];
        }

        return new ConversationDetailOutput(
            id: $conversation->getId(),
            isGroup: $conversation->getType() === Conversation::TYPE_GROUP,
            groupName: $conversation->getGroupName(),
            createdAt: $conversation->getCreatedAt()->format('c'),
            participants: $participants
        );
    }
}
