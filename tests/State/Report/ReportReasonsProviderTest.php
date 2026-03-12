<?php

declare(strict_types=1);

namespace App\Tests\State\Report;

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

    public function testProvideReturnsAllReasons(): void
    {
        $operation = $this->createMock(\ApiPlatform\Metadata\Operation::class);

        $results = $this->provider->provide($operation);

        $this->assertIsArray($results);
        $this->assertCount(count(ReportReasonEnum::cases()), $results);
    }

    public function testProvideReturnsReportReasonOutputInstances(): void
    {
        $operation = $this->createMock(\ApiPlatform\Metadata\Operation::class);

        $results = $this->provider->provide($operation);

        foreach ($results as $result) {
            $this->assertInstanceOf(ReportReasonOutput::class, $result);
        }
    }

    public function testProvideReturnsCorrectKeysAndLabels(): void
    {
        $operation = $this->createMock(\ApiPlatform\Metadata\Operation::class);

        $results = $this->provider->provide($operation);

        $keys = array_map(static fn(ReportReasonOutput $o) => $o->key, $results);
        $labels = array_map(static fn(ReportReasonOutput $o) => $o->label, $results);

        $this->assertContains('spam', $keys);
        $this->assertContains('harassment', $keys);
        $this->assertContains('hateful_content', $keys);
        $this->assertContains('offensive', $keys);
        $this->assertContains('other', $keys);

        $this->assertContains('Spam', $labels);
        $this->assertContains('Harcèlement', $labels);
        $this->assertContains('Contenu haineux', $labels);
        $this->assertContains('Injurieux', $labels);
        $this->assertContains('Autre', $labels);
    }
}
