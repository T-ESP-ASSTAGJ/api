<?php

declare(strict_types=1);

namespace App\State\Conversation;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\Conversation\AddParticipantsInput;
use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @implements ProcessorInterface<AddParticipantsInput, Conversation>
 */
final readonly class AddParticipantsProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security,
        private UserRepository $userRepository,
    ) {
    }

    /**
     * @param AddParticipantsInput $data
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return Conversation
     */
    public function process(mixed $data, ?Operation $operation = null, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof AddParticipantsInput) {
            throw new BadRequestHttpException('Invalid input data');
        }

        $conversationId = $uriVariables['id'] ?? null;
        if (!$conversationId) {
            throw new BadRequestHttpException('Conversation ID is required');
        }

        $conversation = $this->em->getRepository(Conversation::class)->find($conversationId);
        if (!$conversation) {
            throw new BadRequestHttpException('Conversation not found');
        }

        if (!$conversation->isGroup()) {
            throw new BadRequestHttpException('Cannot add participants to a private conversation');
        }

        /** @var User|null $currentUser */
        $currentUser = $this->security->getUser();
        if (!$currentUser instanceof User) {
            throw new \RuntimeException('User must be authenticated');
        }

        // Check if current user is an active participant and is admin
        $currentParticipant = null;
        foreach ($conversation->getActiveParticipants() as $participant) {
            if ($participant->getUser()->getId() === $currentUser->getId()) {
                $currentParticipant = $participant;
                break;
            }
        }

        if (!$currentParticipant || !$currentParticipant->isAdmin()) {
            throw new AccessDeniedHttpException('Seuls les administrateurs du groupe peuvent ajouter des participants');
        }

        $addedCount = 0;
        foreach ($data->userIds as $userId) {
            $user = $this->userRepository->find($userId);
            if (!$user) {
                continue;
            }
            $existingParticipant = null;
            foreach ($conversation->getActiveParticipants() as $participant) {
                if ($participant->getUser()->getId() === $user->getId()) {
                    $existingParticipant = $participant;
                    break;
                }
            }

            if ($existingParticipant) {
                continue;
            }

            // Check if user previously left and needs to be re-added to the group
            $previousParticipant = null;
            foreach ($conversation->getParticipants() as $participant) {
                if ($participant->getUser()->getId() === $user->getId() && !$participant->isActive()) {
                    $previousParticipant = $participant;
                    break;
                }
            }

            if ($previousParticipant) {
                $previousParticipant->setLeftAt(null);
                $previousParticipant->setJoinedAt(new \DateTimeImmutable());
                $previousParticipant->setRole(ConversationParticipant::ROLE_MEMBER);
            } else {
                $participant = new ConversationParticipant();
                $participant->setUser($user);
                $participant->setRole(ConversationParticipant::ROLE_MEMBER);
                $conversation->addParticipant($participant);
            }

            ++$addedCount;
        }

        if (0 === $addedCount) {
            $violations = new ConstraintViolationList([
                new ConstraintViolation(
                    'Aucun participant valide n\'a été ajouté',
                    null,
                    [],
                    $data,
                    'userIds',
                    null
                ),
            ]);
            throw new ValidationException($violations);
        }

        $this->em->flush();

        return $conversation;
    }
}
