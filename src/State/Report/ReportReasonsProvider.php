<?php

declare(strict_types=1);

namespace App\State\Report;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Report\ReportReasonOutput;
use App\Entity\Enum\ReportReasonEnum;

/**
 * @implements ProviderInterface<ReportReasonOutput>
 */
final readonly class ReportReasonsProvider implements ProviderInterface
{
    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return ReportReasonOutput[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        return array_map(
            static fn (ReportReasonEnum $reason) => new ReportReasonOutput($reason->value, $reason->getLabel()),
            ReportReasonEnum::cases(),
        );
    }
}
