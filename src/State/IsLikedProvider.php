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
        private isLikedEnricher $isLikedEnricher,
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

        $this->isLikedEnricher->enrich($data, $operation->getClass());

        return $data;
    }
}
