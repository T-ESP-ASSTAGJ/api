<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Report;

use App\ApiResource\Report\ReportReasonOutput;
use PHPUnit\Framework\TestCase;

class ReportReasonOutputTest extends TestCase
{
    public function testConstructor(): void
    {
        $output = new ReportReasonOutput('spam', 'Spam');

        $this->assertSame('spam', $output->key);
        $this->assertSame('Spam', $output->label);
    }

    public function testPublicProperties(): void
    {
        $output = new ReportReasonOutput('harassment', 'Harcèlement');

        $this->assertSame('harassment', $output->key);
        $this->assertSame('Harcèlement', $output->label);
    }
}
