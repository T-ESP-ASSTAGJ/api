<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Track;

use App\ApiResource\Track\TrackGetOutput;
use App\Entity\Track;
use App\Util\ReflectionUtil;
use PHPUnit\Framework\TestCase;

class TrackGetOutputTest extends TestCase
{
    public function testFromEntity(): void
    {
        $track = new Track();
        $track->setSongId('spotify:track:123456789');
        $track->setTitle('Test Track');
        $track->setArtistName('Test Artist');
        $track->setReleaseYear(2024);
        $track->setCoverImage('https://example.com/cover.jpg');

        ReflectionUtil::setPropertyValue($track, 'id', 123);

        $createdAt = new \DateTimeImmutable('2025-01-01 10:00:00');
        $updatedAt = new \DateTimeImmutable('2025-01-02 15:30:00');
        ReflectionUtil::setPropertyValue($track, 'createdAt', $createdAt);
        ReflectionUtil::setPropertyValue($track, 'updatedAt', $updatedAt);

        $output = TrackGetOutput::fromEntity($track);

        $this->assertSame(123, $output->id);
        $this->assertSame('spotify:track:123456789', $output->songId);
        $this->assertSame('Test Track', $output->title);
        $this->assertSame('Test Artist', $output->artistName);
        $this->assertSame(2024, $output->releaseYear);
        $this->assertSame('https://example.com/cover.jpg', $output->coverImage);
        $this->assertSame($createdAt, $output->createdAt);
        $this->assertSame($updatedAt, $output->updatedAt);
    }

    public function testFromEntityWithNullValues(): void
    {
        $track = new Track();
        $track->setSongId('spotify:track:987654321');
        $track->setTitle('Test Track');
        $track->setArtistName('Test Artist');
        $track->setReleaseYear(null);
        $track->setCoverImage(null);

        ReflectionUtil::setPropertyValue($track, 'id', 456);

        $createdAt = new \DateTimeImmutable('2025-01-01 10:00:00');
        $updatedAt = new \DateTimeImmutable('2025-01-02 15:30:00');
        ReflectionUtil::setPropertyValue($track, 'createdAt', $createdAt);
        ReflectionUtil::setPropertyValue($track, 'updatedAt', $updatedAt);

        $output = TrackGetOutput::fromEntity($track);

        $this->assertSame(456, $output->id);
        $this->assertSame('spotify:track:987654321', $output->songId);
        $this->assertSame('Test Track', $output->title);
        $this->assertSame('Test Artist', $output->artistName);
        $this->assertNull($output->releaseYear);
        $this->assertNull($output->coverImage);
        $this->assertSame($createdAt, $output->createdAt);
        $this->assertSame($updatedAt, $output->updatedAt);
    }
}
