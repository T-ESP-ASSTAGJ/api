<?php

declare(strict_types=1);

namespace App\State\Like;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Like\LikeCreateInput;
use App\Entity\Enum\LikeableTypeEnum;
use App\Entity\Like;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<LikeCreateInput,>
 */
final readonly class LikeProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private EntityManagerInterface $em,
        private Security $security,
    ) {
    }

    /**
     * @param LikeCreateInput      $data
     * @param Operation|null       $operation
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function process($data, $operation, array $uriVariables = [], array $context = []): void
    {
        $entityClass = $data->entityClass->toEntityClass();

        /** @var LikeableTypeEnum|null $entityToLike */
        $entityToLike = $this->em->getRepository($entityClass)->find($data->entityId);

        if (!$entityToLike) {
            throw new NotFoundHttpException(sprintf('Likeable Entity %s with id %d not found.', $entityClass, $data->entityId));
        }

        /** @var $user User */
        $user = $this->security->getUser();

        $like = new Like();
        $like
            ->setEntityClass($data->entityClass)
            ->setEntityId($data->entityId)
            ->setUser($user);

        try {
            $this->persistProcessor->process(
                $like,
                $operation,
                $uriVariables,
                $context
            );
        } catch (\Throwable) {
            throw new BadRequestHttpException('You have already liked this entity.');
        }
    }
}
