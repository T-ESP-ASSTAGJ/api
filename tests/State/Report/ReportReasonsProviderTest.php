<?php

declare(strict_types=1);

namespace App\Tests\State\Report;

use ApiPlatform\Metadata\GetCollection;
use App\ApiResource\Report\ReportReasonOutput;
use App\Entity\Enum\ReportReasonEnum;
use App\State\Report\ReportReasonsProvider;
use PHPUnit\Framework\TestCase;

class ReportReasonsProviderTest extends TestCase
{
    private ReportReasonsProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new ReportReasonsProvider();
    }

    public function testProvidesAllReportReasons(): void
    {
        $result = $this->provider->provide(new GetCollection());

        $this->assertCount(\count(ReportReasonEnum::cases()), $result);
        $this->assertContainsOnlyInstancesOf(ReportReasonOutput::class, $result);
    }

    public function testOutputContainsCorrectKeysAndLabels(): void
    {
        $result = $this->provider->provide(new GetCollection());

        $keys = array_map(static fn (ReportReasonOutput $o) => $o->key, $result);
        $labels = array_map(static fn (ReportReasonOutput $o) => $o->label, $result);

        foreach (ReportReasonEnum::cases() as $reason) {
            $this->assertContains($reason->value, $keys);
            $this->assertContains($reason->getLabel(), $labels);
        }
    }
}
