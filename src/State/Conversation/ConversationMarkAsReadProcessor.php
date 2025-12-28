<?php

declare(strict_types=1);

namespace App\State\Conversation;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Conversation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @implements ProcessorInterface<Conversation, void>
 */
final readonly class ConversationMarkAsReadProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
    ) {
    }

    /**
     * @param Conversation         $data
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        if (null === $user) {
            throw new UnauthorizedHttpException('Bearer', 'Authentication required');
        }

        // Find the participant for this user in this conversation
        $participant = null;
        foreach ($data->getActiveParticipants() as $p) {
            if ($p->getUser()->getId() === $user->getId()) {
                $participant = $p;
                break;
            }
        }

        if (null === $participant) {
            throw new AccessDeniedHttpException('You are not a participant of this conversation');
        }

        // Reset unread count
        $participant->resetUnreadCount();

        $this->entityManager->flush();
    }
}
