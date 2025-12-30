<?php

declare(strict_types=1);

namespace App\Tests\Entity\Enum;

use App\Entity\Enum\MusicPlatformEnum;
use PHPUnit\Framework\TestCase;

class MusicPlatformEnumTest extends TestCase
{
    public function testEnumCases(): void
    {
        $this->assertSame('spotify', MusicPlatformEnum::Spotify->value);
        $this->assertSame('deezer', MusicPlatformEnum::Deezer->value);
        $this->assertSame('soundcloud', MusicPlatformEnum::SoundCloud->value);
    }

    public function testValues(): void
    {
        $values = MusicPlatformEnum::values();

        $this->assertIsArray($values);
        $this->assertCount(3, $values);
        $this->assertContains('spotify', $values);
        $this->assertContains('deezer', $values);
        $this->assertContains('soundcloud', $values);
    }
}