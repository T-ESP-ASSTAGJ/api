<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Track;
use PHPUnit\Framework\TestCase;

class TrackTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $track = new Track();

        $this->assertNull($track->getId());

        $result = $track->setSongId('spotify:track:123456');
        $this->assertSame($track, $result);
        $this->assertSame('spotify:track:123456', $track->getSongId());

        $result = $track->setTitle('Test Track');
        $this->assertSame($track, $result);
        $this->assertSame('Test Track', $track->getTitle());

        $result = $track->setArtistName('Test Artist');
        $this->assertSame($track, $result);
        $this->assertSame('Test Artist', $track->getArtistName());

        $result = $track->setReleaseYear(2024);
        $this->assertSame($track, $result);
        $this->assertSame(2024, $track->getReleaseYear());

        $result = $track->setCoverImage('https://example.com/cover.jpg');
        $this->assertSame($track, $result);
        $this->assertSame('https://example.com/cover.jpg', $track->getCoverImage());
    }

    public function testNullableFields(): void
    {
        $track = new Track();

        $this->assertNull($track->getReleaseYear());
        $this->assertNull($track->getCoverImage());

        $track->setReleaseYear(null);
        $track->setCoverImage(null);

        $this->assertNull($track->getReleaseYear());
        $this->assertNull($track->getCoverImage());
    }

    public function testTimeStampableTrait(): void
    {
        $track = new Track();
        $track->setCreatedAt();

        $this->assertInstanceOf(\DateTimeImmutable::class, $track->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $track->getUpdatedAt());
    }
}
