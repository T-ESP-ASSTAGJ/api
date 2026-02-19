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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<Conversation, void>
 */
class ConversationDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        if (!$data instanceof Conversation) {
            throw new NotFoundHttpException('Conversation not found.');
        }

        /** @var User $user */
        $user = $this->security->getUser();

        if (!$data->hasUser($user)) {
            throw new AccessDeniedHttpException('You are not a participant of this conversation.');
        }

        if (!$data->isAdmin($user)) {
            throw new AccessDeniedHttpException('You are not admin of this conversation.');
        }

        $this->entityManager->remove($data);
        $this->entityManager->flush();
    }
}