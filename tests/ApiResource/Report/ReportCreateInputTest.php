<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Report;

use App\ApiResource\Report\ReportCreateInput;
use App\Entity\Enum\ReportReasonEnum;
use App\Entity\Enum\ReportableTypeEnum;
use PHPUnit\Framework\TestCase;

class ReportCreateInputTest extends TestCase
{
    public function testPublicProperties(): void
    {
        $input = new ReportCreateInput();
        $input->entityClass = ReportableTypeEnum::Post;
        $input->entityId = 1;
        $input->reason = ReportReasonEnum::Spam;
        $input->message = 'Ceci est du spam.';

        $this->assertSame(ReportableTypeEnum::Post, $input->entityClass);
        $this->assertSame(1, $input->entityId);
        $this->assertSame(ReportReasonEnum::Spam, $input->reason);
        $this->assertSame('Ceci est du spam.', $input->message);
    }

    public function testMessageIsNullByDefault(): void
    {
        $input = new ReportCreateInput();

        $this->assertNull($input->message);
    }
}
