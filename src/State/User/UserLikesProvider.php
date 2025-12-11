<?php

declare(strict_types=1);

namespace App\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Enum\LikeableTypeEnum;
use App\Entity\Like;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<array>
 */
final readonly class UserLikesProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param Operation $operation
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return array
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $user = $this->entityManager->getRepository(User::class)->find($uriVariables['id']);

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        $likes = $this->entityManager->getRepository(Like::class)->findBy(['user' => $user]);

        if (empty($likes)) {
            return [];
        }

        // Array ['Post' => [1, 3], 'Comment' => [2, 4]]
        $map = [];
        foreach ($likes as $like) {
            $entityClass = LikeableTypeEnum::from($like->getEntityClass())->toEntityClass();
            $map[$entityClass][] = $like->getEntityId();
        }

        $fetchedEntities = [];
        foreach ($map as $className => $ids) {
            $results = $this->entityManager->getRepository($className)->findBy(['id' => $ids]);

            foreach ($results as $entity) {
                $fetchedEntities[$className][$entity->getId()] = $entity;
            }
        }

        foreach ($likes as $like) {
            $class = LikeableTypeEnum::from($like->getEntityClass())->toEntityClass();
            $id = $like->getEntityId();

            if (isset($fetchedEntities[$class][$id])) {
                $like->setLikedEntity($fetchedEntities[$class][$id]);
            }
        }

        return $likes;
    }
}
