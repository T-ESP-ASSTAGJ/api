<?php

declare(strict_types=1);

namespace App\State\Conversation;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<Conversation, JsonResponse>
 */
final readonly class ConversationLeaveProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security,
    ) {
    }

    /**
     * @param Conversation         $data
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return JsonResponse
     */
    public function process(mixed $data, ?Operation $operation = null, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof Conversation) {
            throw new \InvalidArgumentException('Expected Conversation entity');
        }

        /** @var User|null $currentUser */
        $currentUser = $this->security->getUser();
        if (!$currentUser instanceof User) {
            throw new \RuntimeException('User must be authenticated');
        }

        $participant = $data->getParticipants()->filter(
            fn (ConversationParticipant $p) => $p->getUser() === $currentUser && null === $p->getLeftAt()
        )->first();

        if (!$participant) {
            throw new NotFoundHttpException('Vous n\'êtes pas membre de cette conversation');
        }

        // Mark as left
        $participant->leave();
        $this->em->flush();

        // Check if all participants have left (for groups)
        if ($data->isGroup() && 0 === $data->getActiveParticipants()->count()) {
            // Delete the conversation
            $this->em->remove($data);
            $this->em->flush();

            return new JsonResponse([
                'message' => 'Vous avez quitté le groupe. Le groupe a été supprimé car vous étiez le dernier membre.',
                'left_at' => $participant->getLeftAt()?->format('c'),
                'conversation_deleted' => true,
            ]);
        }

        return new JsonResponse([
            'message' => 'Vous avez quitté '.($data->isGroup() ? 'le groupe' : 'la conversation'),
            'left_at' => $participant->getLeftAt()?->format('c'),
        ]);
    }
}
