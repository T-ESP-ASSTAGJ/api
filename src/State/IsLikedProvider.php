<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Interface\LikeableInterface;
use App\Service\isLikedEnricher;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Provider décorateur qui enrichit les entités Likeable avec la propriété virtuelle `isLiked`.
 *
 * Délègue la récupération réelle des données aux providers Doctrine ORM standard (item/collection),
 * puis transmet le résultat à {@see isLikedEnricher} pour annoter chaque entité selon que
 * l'utilisateur authentifié courant l'a aimée ou non. Utilisé par les opérations Post et Comment.
 *
 * @implements ProviderInterface<LikeableInterface|object>
 */
final readonly class IsLikedProvider implements ProviderInterface
{
    public function __construct(
        /**
         * @var ProviderInterface<object>
         */
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface $itemProvider,
        /**
         * @var ProviderInterface<object> $collectionProvider
         */
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')]
        private ProviderInterface $collectionProvider,
        private isLikedEnricher $isLikedEnricher,
    ) {
    }

    // Ce provider calcule la propriété virtuelle isLiked tout en retournant l'objet ou la collection standard
    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return LikeableInterface|iterable<LikeableInterface>|null
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // Délégation de la récupération des données aux providers décorés
        if ($operation instanceof CollectionOperationInterface) {
            $data = $this->collectionProvider->provide($operation, $uriVariables, $context);
        } else {
            $data = $this->itemProvider->provide($operation, $uriVariables, $context);
        }

        $this->isLikedEnricher->enrich($data, $operation->getClass());

        return $data;
    }
}
