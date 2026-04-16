<?php

declare(strict_types=1);

namespace App\Tests\Entity\Enum;

use App\Entity\Enum\MessageTypeEnum;
use PHPUnit\Framework\TestCase;

class MessageTypeEnumTest extends TestCase
{
    public function testEnumCases(): void
    {
        $this->assertSame('text', MessageTypeEnum::Text->value);
        $this->assertSame('music', MessageTypeEnum::Music->value);
        $this->assertSame('image', MessageTypeEnum::Image->value);
        $this->assertSame('share', MessageTypeEnum::Share->value);
    }

    public function testValues(): void
    {
        $values = MessageTypeEnum::values();

        $this->assertCount(4, $values);

        $this->assertEqualsCanonicalizing(['music', 'text', 'image', 'share'], $values);
    }
}
