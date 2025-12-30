<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Enum\LikeableTypeEnum;
use App\Entity\Interface\LikeableInterface;
use App\Entity\Like;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ProviderInterface<LikeableInterface|object>
 */
final readonly class IsLikedProvider implements ProviderInterface
{
    public function __construct(
        /** @var ProviderInterface<object> */
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface $itemProvider,
        /** @var ProviderInterface<object> $collectionProvider */
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')]
        private ProviderInterface $collectionProvider,
        private Security $security,
        private EntityManagerInterface $entityManager,
    ) {
    }

    // This provider handles the calculation of isLiked virtual property, while returning the classic object / collection
    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return LikeableInterface|iterable<LikeableInterface>|null
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // Fetch data using the decorated providers
        if ($operation instanceof CollectionOperationInterface) {
            $data = $this->collectionProvider->provide($operation, $uriVariables, $context);
        } else {
            $data = $this->itemProvider->provide($operation, $uriVariables, $context);
        }

        $user = $this->security->getUser();
        $resourceClass = $operation->getClass();

        // If no user or resource is not Likeable, return data as is
        if (!$user || !is_a($resourceClass, LikeableInterface::class, true)) {
            return $data;
        }

        $enum = LikeableTypeEnum::tryFrom($resourceClass);
        if (!$enum) {
            return $data;
        }

        // Handle Collection or Item
        if (is_iterable($data)) {
            $this->handleCollection($data, $user, $enum);
        } elseif ($data instanceof LikeableInterface) {
            $this->handleItem($data, $user, $enum);
        }

        return $data;
    }

    /**
     * @param iterable<LikeableInterface> $collection
     */
    private function handleCollection(iterable $collection, object $user, LikeableTypeEnum $enum): void
    {
        $entities = [];

        foreach ($collection as $entity) {
            if ($id = $entity->getId()) {
                $entities[$id] = $entity;
            }
        }

        if (empty($entities)) {
            return;
        }

        $ids = array_keys($entities);

        $likedIds = $this->entityManager->createQueryBuilder()
            ->select('l.entityId')
            ->from(Like::class, 'l')
            ->where('l.user = :user')
            ->andWhere('l.entityClass = :class')
            ->andWhere('l.entityId IN (:ids)')
            ->setParameter('user', $user)
            ->setParameter('class', $enum)
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getSingleColumnResult();

        foreach ($likedIds as $id) {
            if (isset($entities[$id])) {
                $entities[$id]->setIsLiked(true);
            }
        }
    }

    private function handleItem(LikeableInterface $entity, object $user, LikeableTypeEnum $enum): void
    {
        $count = $this->entityManager->getRepository(Like::class)->count([
            'user' => $user,
            'entityClass' => $enum,
            'entityId' => $entity->getId(),
        ]);

        $entity->setIsLiked($count > 0);
    }
}
