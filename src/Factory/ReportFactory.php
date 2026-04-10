<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Enum\ReportableTypeEnum;
use App\Entity\Enum\ReportReasonEnum;
use App\Entity\Report;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Report>
 *
 * @codeCoverageIgnore
 */
final class ReportFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return Report::class;
    }

    /**
     * @return array<string, mixed>
     */
    #[\Override]
    protected function defaults(): array
    {
        return [
            'user' => UserFactory::new(),
            'entityId' => self::faker()->numberBetween(1, 100),
            'entityClass' => self::faker()->randomElement(ReportableTypeEnum::cases()),
            'reason' => self::faker()->randomElement(ReportReasonEnum::cases()),
            'message' => self::faker()->optional(0.5)->sentence(),
        ];
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this;
    }
}
