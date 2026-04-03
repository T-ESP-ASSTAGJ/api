<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Message;
use App\Service\IsReadEnricher;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ProviderInterface<Message|object>
 */
final readonly class IsReadProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface $itemProvider,
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')]
        private ProviderInterface $collectionProvider,
        private IsReadEnricher $isReadEnricher,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $data = $operation instanceof CollectionOperationInterface
            ? $this->collectionProvider->provide($operation, $uriVariables, $context)
            : $this->itemProvider->provide($operation, $uriVariables, $context);

        $this->isReadEnricher->enrich($data);

        return $data;
    }
}
