<?php

declare(strict_types=1);

namespace App\Tests\Entity\Enum;

use App\Entity\Enum\MercureTypeEnum;
use PHPUnit\Framework\TestCase;

class MercureTypeEnumTest extends TestCase
{
    public function testEnumCases(): void
    {
        $this->assertSame('message', MercureTypeEnum::Message->value);
    }

    public function testValues(): void
    {
        $values = MercureTypeEnum::values();

        $this->assertCount(1, $values);
        $this->assertContains('message', $values);
    }
}
