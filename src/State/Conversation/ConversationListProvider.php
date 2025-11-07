<?php

declare(strict_types=1);

namespace App\State\Conversation;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Conversation\ConversationGetOutput;
use App\Entity\Conversation;
use App\Entity\User;
use App\Repository\ConversationRepository;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProviderInterface<ConversationGetOutput>
 */
final readonly class ConversationListProvider implements ProviderInterface
{
    public function __construct(
        private Security $security,
        private ConversationRepository $conversationRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new \RuntimeException('User must be authenticated');
        }

        $conversations = $this->conversationRepository->findByUser($user);

        if (empty($conversations)) {
            return [
                'conversations' => [],
                'total_count' => 0,
                'message' => 'Aucune conversation pour le moment',
            ];
        }

        $conversationOutputs = array_map(
            fn (Conversation $conversation) => $this->mapToOutput($conversation, $user),
            $conversations
        );

        return [
            'conversations' => $conversationOutputs,
            'total_count' => count($conversationOutputs),
        ];
    }

    private function mapToOutput(Conversation $conversation, User $currentUser): ConversationGetOutput
    {
        // Get the other participant (for private conversations)
        $participant = null;
        if ($conversation->getType() === Conversation::TYPE_PRIVATE) {
            $activeParticipants = $conversation->getActiveParticipants();
            foreach ($activeParticipants as $p) {
                if ($p->getId() !== $currentUser->getId()) {
                    $participant = [
                        'id' => $p->getId(),
                        'username' => $p->getUsername(),
                        'profile_picture' => $p->getProfilePicture(),
                    ];
                    break;
                }
            }
        }

        // Get last message
        $lastMessage = $conversation->getLastMessage();
        $lastMessageData = null;
        if ($lastMessage) {
            $preview = $lastMessage->getContent();
            if ($lastMessage->isMusicMessage() && $lastMessage->getTrackMetadata()) {
                $trackMetadata = $lastMessage->getTrackMetadata();
                $preview = sprintf(
                    '🎵 %s - %s',
                    $trackMetadata['title'] ?? 'Unknown',
                    $trackMetadata['artist'] ?? 'Unknown'
                );
            }

            $lastMessageData = [
                'id' => $lastMessage->getId(),
                'type' => $lastMessage->getType(),
                'content' => $lastMessage->getContent(),
                'preview' => $preview,
                'author' => [
                    'id' => $lastMessage->getAuthor()->getId(),
                    'username' => $lastMessage->getAuthor()->getUsername(),
                ],
                'created_at' => $lastMessage->getCreatedAt()->format('c'),
            ];
        }

        return new ConversationGetOutput(
            id: $conversation->getId(),
            type: $conversation->getType(),
            participant: $participant,
            lastMessage: $lastMessageData,
            unreadCount: $conversation->getUnreadCountForUser($currentUser),
            updatedAt: $conversation->getUpdatedAt()->format('c')
        );
    }
}
