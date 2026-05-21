<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Enum\LikeableTypeEnum;
use App\Entity\Interface\LikeableInterface;
use App\Entity\Like;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Enrichit les entités Likeable avec un drapeau `isLiked` calculé pour l'utilisateur authentifié courant.
 *
 * Pour les collections, une seule requête groupée est utilisée pour éviter les requêtes N+1.
 * Pour les requêtes non authentifiées ou les ressources non-Likeable, l'enrichissement est sans effet.
 */
readonly class isLikedEnricher
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param object|iterable<object>|null $data
     */
    public function enrich(mixed $data, string $resourceClass): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        // Pas d'enrichissement si l'utilisateur n'est pas connecté ou si la ressource n'est pas Likeable
        if (!$user || !is_a($resourceClass, LikeableInterface::class, true)) {
            return;
        }

        $enum = LikeableTypeEnum::tryFrom($resourceClass);
        if (!$enum) {
            return;
        }

        // Traitement selon le type : collection ou élément unique
        if (is_iterable($data)) {
            $this->handleCollection($data, $user, $enum);
        } elseif ($data instanceof LikeableInterface) {
            $this->handleItem($data, $user, $enum);
        }
    }

    /**
     * @param iterable<object> $collection
     */
    private function handleCollection(iterable $collection, User $user, LikeableTypeEnum $enum): void
    {
        $entities = [];

        foreach ($collection as $entity) {
            if ($entity instanceof LikeableInterface && $id = $entity->getId()) {
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
            ->setParameter('class', $enum->value)
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getSingleColumnResult()
        ;

        foreach ($likedIds as $id) {
            if (isset($entities[$id])) {
                $entities[$id]->setIsLiked(true);
            }
        }
    }

    private function handleItem(LikeableInterface $entity, User $user, LikeableTypeEnum $enum): void
    {
        $count = $this->entityManager->getRepository(Like::class)->count([
            'user' => $user,
            'entityClass' => $enum->value,
            'entityId' => $entity->getId(),
        ]);

        $entity->setIsLiked($count > 0);
    }
}
