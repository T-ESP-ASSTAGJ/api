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
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements ProcessorInterface<ConversationCreateInput, Conversation>
 */
final readonly class ConversationCreateProcessor implements ProcessorInterface
{
    public function __construct(
        /** @var ProcessorInterface<Conversation, Conversation> */
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private ValidatorInterface $validator,
        private Security $security,
        private UserRepository $userRepository,
    ) {
    }

    /**
     * @param ConversationCreateInput $data
     * @param array<string, mixed>    $uriVariables
     * @param array<string, mixed>    $context
     */
    public function process($data, ?Operation $operation = null, array $uriVariables = [], array $context = []): Conversation
    {
        $conversation = new Conversation();
        $conversation->setIsGroup($data->isGroup);
        $conversation->setGroupName($data->groupName);

        /** @var User|null $currentUser */
        $currentUser = $this->security->getUser();
        if (!$currentUser instanceof User) {
            throw new \RuntimeException('User must be authenticated');
        }

        if ($data->isGroup && empty($data->groupName)) {
            $violations = new ConstraintViolationList([
                new ConstraintViolation(
                    'Le nom du groupe est obligatoire',
                    null,
                    [],
                    $data,
                    'groupName',
                    null
                ),
            ]);
            throw new ValidationException($violations);
        }

        if ($data->isGroup && (null === $data->participants || 0 === count($data->participants))) {
            $violations = new ConstraintViolationList([
                new ConstraintViolation(
                    'Vous devez sélectionner au moins un participant',
                    null,
                    [],
                    $data,
                    'participants',
                    null
                ),
            ]);
            throw new ValidationException($violations);
        }

        $creatorParticipant = new ConversationParticipant();
        $creatorParticipant->setUser($currentUser);
        $creatorParticipant->setRole(ConversationParticipant::ROLE_ADMIN);
        $conversation->addParticipant($creatorParticipant);

        if (null !== $data->participants && count($data->participants) > 0) {
            foreach ($data->participants as $userId) {
                $user = $this->userRepository->find($userId);
                if (!$user) {
                    continue;
                }

                if ($user->getId() === $currentUser->getId()) {
                    continue;
                }

                $participant = new ConversationParticipant();
                $participant->setUser($user);
                $conversation->addParticipant($participant);
            }
        }

        $violations = $this->validator->validate($conversation);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        return $this->persistProcessor->process($conversation, $operation, $uriVariables, $context);
    }
}
