<?php

declare(strict_types=1);

namespace App\State\Conversation;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Conversation;
use App\Entity\User;
use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProcessorInterface<null, null>
 */
final readonly class ConversationLeaveProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security,
        private ConversationRepository $conversationRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function process(mixed $data, ?Operation $operation = null, array $uriVariables = [], array $context = []): null
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

        // Check if user is an active participant
        if (!$conversation->isUserParticipant($currentUser)) {
            throw new \RuntimeException('You are not a participant of this conversation');
        }

        // Mark user as left
        foreach ($conversation->getConversationParticipants() as $cp) {
            if ($cp->getUser()->getId() === $currentUser->getId() && $cp->isActive()) {
                $cp->setLeftAt(new \DateTimeImmutable());
                break;
            }
        }

        $this->em->flush();

        // Check if conversation should be deleted (no active participants)
        if ($conversation->shouldBeDeleted()) {
            $this->em->remove($conversation);
            $this->em->flush();
        }

        return null;
    }
}
