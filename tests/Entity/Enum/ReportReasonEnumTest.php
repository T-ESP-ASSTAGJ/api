<?php

declare(strict_types=1);

namespace App\Tests\Entity\Enum;

use App\Entity\Enum\ReportReasonEnum;
use PHPUnit\Framework\TestCase;

class ReportReasonEnumTest extends TestCase
{
    public function testEnumCases(): void
    {
        $this->assertSame('spam', ReportReasonEnum::Spam->value);
        $this->assertSame('harassment', ReportReasonEnum::Harassment->value);
        $this->assertSame('hateful_content', ReportReasonEnum::HatefulContent->value);
        $this->assertSame('offensive', ReportReasonEnum::Offensive->value);
        $this->assertSame('other', ReportReasonEnum::Other->value);
    }

    public function testGetLabel(): void
    {
        $this->assertSame('Spam', ReportReasonEnum::Spam->getLabel());
        $this->assertSame('Harcèlement', ReportReasonEnum::Harassment->getLabel());
        $this->assertSame('Contenu haineux', ReportReasonEnum::HatefulContent->getLabel());
        $this->assertSame('Injurieux', ReportReasonEnum::Offensive->getLabel());
        $this->assertSame('Autre', ReportReasonEnum::Other->getLabel());
    }

    public function testValues(): void
    {
        $values = ReportReasonEnum::values();

        $this->assertCount(5, $values);
        $this->assertContains('spam', $values);
        $this->assertContains('harassment', $values);
        $this->assertContains('hateful_content', $values);
        $this->assertContains('offensive', $values);
        $this->assertContains('other', $values);
    }
}
