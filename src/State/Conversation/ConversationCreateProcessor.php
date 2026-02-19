<?php

declare(strict_types=1);

namespace App\State\Conversation;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\Conversation\ConversationCreateInput;
use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use App\Entity\User;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements ProcessorInterface<ConversationCreateInput, Conversation>
 */
final readonly class ConversationCreateProcessor implements ProcessorInterface
{
    public function __construct(
        private ValidatorInterface $validator,
        private Security           $security,
        private UserRepository     $userRepository,
        private ConversationRepository $conversationRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param ConversationCreateInput $data
     * @param array<string, mixed>    $uriVariables
     * @param array<string, mixed>    $context
     */
    public function process($data, ?Operation $operation = null, array $uriVariables = [], array $context = []): Conversation
    {
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        if (!$data->isGroup && count($data->participants) !== 1) {
            throw new BadRequestException('A private conversation must have exactly one other participant.');
        }

        $targetUser = $this->userRepository->find($data->participants[0]);
        if (!$targetUser) {
            throw new BadRequestException('Recipient not found.');
        }

        if (!$data->isGroup) {
            if ($this->conversationRepository->findPrivateConversation($currentUser, $targetUser)) {
                throw new BadRequestException('This private conversation already exists.');
            }
        }

        // 2. Handle Group Validation
        if ($data->isGroup && empty($data->groupName)) {
            throw new BadRequestException('Group conversations must have a name.');
        }

        $conversation = new Conversation();
        $conversation->setIsGroup($data->isGroup);
        $conversation->setGroupName($data->groupName);

        $creatorParticipant = new ConversationParticipant();
        $creatorParticipant->setUser($currentUser);
        $creatorParticipant->setRole(ConversationParticipant::ROLE_ADMIN);
        $conversation->addParticipant($creatorParticipant);

        foreach ($data->participants as $userId) {
            $user = $this->userRepository->find($userId);

            // Check if user exists, isn't the creator, and isn't already in the collection
            if (!$user || $user === $currentUser || $conversation->hasUser($user)) {
                continue;
            }

            $participant = new ConversationParticipant();
            $participant->setUser($user);
            $participant->setRole(ConversationParticipant::ROLE_MEMBER);
            $conversation->addParticipant($participant);
        }

        $violations = $this->validator->validate($conversation);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $this->entityManager->persist($conversation);
        $this->entityManager->flush();

        return $conversation;
    }
}
