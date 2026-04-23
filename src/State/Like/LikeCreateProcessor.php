<?php

declare(strict_types=1);

namespace App\State\Like;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Like\LikeCreateInput;
use App\Entity\Comment;
use App\Entity\Interface\LikeableInterface;
use App\Entity\Like;
use App\Entity\Post;
use App\Entity\User;
use App\Message\LikeCreatedMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @implements ProcessorInterface<LikeCreateInput, void>
 */
final readonly class LikeCreateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
        private MessageBusInterface $bus,
    ) {
    }

    /**
     * @param LikeCreateInput $data
     * @param Operation|null $operation
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function process($data, $operation, array $uriVariables = [], array $context = []): void
    {
        $entityClass = $data->entityClass->value;

        /** @var LikeableInterface|null $entityToLike */
        $entityToLike = $this->entityManager->getRepository($entityClass)->find($data->entityId);

        if (!$entityToLike) {
            throw new NotFoundHttpException(\sprintf('Likeable Entity %s with id %d not found.', $entityClass, $data->entityId));
        }

        /** @var User $user */
        $user = $this->security->getUser();

        $owner = $entityToLike->getUser();
        if ($owner->getId() === $user->getId()) {
            throw new BadRequestHttpException('Cannot like your own content.');
        }

        $like = new Like();
        $like
            ->setEntityClass($data->entityClass)
            ->setEntityId($data->entityId)
            ->setUser($user)
        ;

        $content = null;

        if ($entityToLike instanceof Comment) {
            $content = $entityToLike->getPost();
        }

        if ($entityToLike instanceof Post) {
            $content = $entityToLike;
        }

        try {
            $this->entityManager->persist($like);
            $this->entityManager->flush();
            $this->bus->dispatch(new LikeCreatedMessage(
                $user->getId(),
                $owner->getId(),
                $like->getId(),
                $content->getId(),
            ));
        } catch (\Throwable) {
            throw new BadRequestHttpException('You have already liked this entity.');
        }
    }
}
