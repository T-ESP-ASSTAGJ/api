<?php

declare(strict_types=1);

namespace App\State\Conversation;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\Conversation\RemoveParticipantsInput;
use App\Entity\Conversation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @implements ProcessorInterface<RemoveParticipantsInput, Conversation|JsonResponse>
 */
final readonly class RemoveParticipantsProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security,
    ) {
    }

    /**
     * @param RemoveParticipantsInput $data
     * @param array<string, mixed>    $uriVariables
     * @param array<string, mixed>    $context
     *
     * @return Conversation|JsonResponse
     */
    public function process(mixed $data, ?Operation $operation = null, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof RemoveParticipantsInput) {
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
            throw new BadRequestHttpException('Cannot remove participants from a private conversation');
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
            throw new AccessDeniedHttpException('Seuls les administrateurs du groupe peuvent retirer des participants');
        }

        $removedCount = 0;
        foreach ($data->userIds as $userId) {
            // Prevent removing self - user should use /leave endpoint instead
            if ($userId === $currentUser->getId()) {
                continue;
            }
            $participantToRemove = null;
            foreach ($conversation->getActiveParticipants() as $participant) {
                if ($participant->getUser()->getId() === $userId) {
                    $participantToRemove = $participant;
                    break;
                }
            }

            if (!$participantToRemove) {
                continue;
            }

            $participantToRemove->leave();
            ++$removedCount;
        }

        if (0 === $removedCount) {
            $violations = new ConstraintViolationList([
                new ConstraintViolation(
                    'Aucun participant valide n\'a été retiré',
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

        // Check if group should be auto-deleted (last member left)
        if (0 === $conversation->getActiveParticipants()->count()) {
            $this->em->remove($conversation);
            $this->em->flush();

            return new JsonResponse([
                'message' => 'Les derniers membres ont été retirés. Le groupe a été supprimé.',
                'conversation_deleted' => true,
            ]);
        }

        return $conversation;
    }
}
