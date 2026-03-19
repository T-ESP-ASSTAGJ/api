<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Message;
use App\Service\IsReadEnricher;
use App\State\Message\MessageGetProvider;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ProviderInterface<Message|object>
 */
final readonly class IsReadProvider implements ProviderInterface
{
    public function __construct(
            /** @var ProviderInterface<object> */
        #[Autowire(service: MessageGetProvider::class)]
        private ProviderInterface $itemProvider,
            /** @var ProviderInterface<object> $collectionProvider */
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')]
        private ProviderInterface $collectionProvider,
        private IsReadEnricher $isReadEnricher,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return Message|iterable<Message>|null
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            $data = $this->collectionProvider->provide($operation, $uriVariables, $context);
        } else {
            $data = $this->itemProvider->provide($operation, $uriVariables, $context);
        }

        $this->isReadEnricher->enrich($data);

        return $data;
    }
}
