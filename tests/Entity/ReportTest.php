<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Enum\ReportReasonEnum;
use App\Entity\Enum\ReportableTypeEnum;
use App\Entity\Report;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ReportTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $report = new Report();
        $user = new User();

        $this->assertNull($report->getId());

        $result = $report->setUser($user);
        $this->assertSame($report, $result);
        $this->assertSame($user, $report->getUser());

        $result = $report->setEntityId(123);
        $this->assertSame($report, $result);
        $this->assertSame(123, $report->getEntityId());

        $result = $report->setEntityClass(ReportableTypeEnum::Post);
        $this->assertSame($report, $result);
        $this->assertSame(ReportableTypeEnum::Post, $report->getEntityClass());

        $result = $report->setReason(ReportReasonEnum::Spam);
        $this->assertSame($report, $result);
        $this->assertSame(ReportReasonEnum::Spam, $report->getReason());

        $result = $report->setMessage('This content is spam');
        $this->assertSame($report, $result);
        $this->assertSame('This content is spam', $report->getMessage());

        $result = $report->setMessage(null);
        $this->assertSame($report, $result);
        $this->assertNull($report->getMessage());
    }

    public function testTimeStampableTrait(): void
    {
        $report = new Report();
        $report->setCreatedAt();

        $this->assertInstanceOf(\DateTimeImmutable::class, $report->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $report->getUpdatedAt());
    }
}