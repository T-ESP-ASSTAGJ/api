<?php

declare(strict_types=1);

namespace App\State\Conversation;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\Conversation\ConversationCreateInput;
use App\ApiResource\Conversation\ConversationDetailOutput;
use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use App\Entity\Follow;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements ProcessorInterface<ConversationCreateInput, ConversationDetailOutput>
 */
final readonly class ConversationCreateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
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
    public function process(mixed $data, ?Operation $operation = null, array $uriVariables = [], array $context = []): ConversationDetailOutput
    {
        if (!$data instanceof ConversationCreateInput) {
            throw new \InvalidArgumentException('Invalid input data');
        }

        /** @var User|null $currentUser */
        $currentUser = $this->security->getUser();
        if (!$currentUser instanceof User) {
            throw new \RuntimeException('User must be authenticated');
        }

        // Validate group requirements
        if ($data->isGroup && empty($data->groupName)) {
            throw new ValidationException('Le nom du groupe est obligatoire');
        }

        // Create conversation
        $conversation = new Conversation();
        $conversation->setType($data->isGroup ? Conversation::TYPE_GROUP : Conversation::TYPE_PRIVATE);

        if ($data->isGroup) {
            $conversation->setGroupName($data->groupName);
        }

        // Add creator as participant
        $creatorParticipant = new ConversationParticipant();
        $creatorParticipant->setUser($currentUser);
        $creatorParticipant->setConversation($conversation);
        $conversation->addConversationParticipant($creatorParticipant);

        // Add other participants
        if (!empty($data->participants)) {
            // Get user's followers and following for validation
            $followedUserIds = $this->getFollowedUserIds($currentUser);

            foreach ($data->participants as $participantId) {
                // Skip creator
                if ($participantId === $currentUser->getId()) {
                    continue;
                }

                // Validate participant is in followers/following
                if (!in_array($participantId, $followedUserIds, true)) {
                    throw new ValidationException(sprintf(
                        'L\'utilisateur %d n\'est pas dans vos abonnements/abonnés',
                        $participantId
                    ));
                }

                /** @var User|null $participantUser */
                $participantUser = $this->userRepository->find($participantId);
                if (!$participantUser) {
                    throw new ValidationException(sprintf('Utilisateur %d non trouvé', $participantId));
                }

                $participant = new ConversationParticipant();
                $participant->setUser($participantUser);
                $participant->setConversation($conversation);
                $conversation->addConversationParticipant($participant);
            }
        }

        // Validate
        $violations = $this->validator->validate($conversation);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        // Persist
        $this->em->persist($conversation);
        $this->em->flush();

        // TODO: Send notifications to participants

        // Return output
        return $this->mapToOutput($conversation);
    }

    /**
     * Get IDs of users that current user follows or is followed by.
     *
     * @return int[]
     */
    private function getFollowedUserIds(User $user): array
    {
        $followRepository = $this->em->getRepository(Follow::class);

        // Get users that current user follows
        $following = $followRepository->findBy(['follower' => $user]);
        $followingIds = array_map(fn (Follow $f) => $f->getFollowedUser()?->getId(), $following);

        // Get users that follow current user
        $followers = $followRepository->findBy(['followedUser' => $user]);
        $followerIds = array_map(fn (Follow $f) => $f->getFollower()?->getId(), $followers);

        return array_unique(array_merge($followingIds, $followerIds));
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
