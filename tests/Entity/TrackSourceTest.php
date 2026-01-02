<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Track;
use App\Entity\TrackSource;
use PHPUnit\Framework\TestCase;

class TrackSourceTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $trackSource = new TrackSource();
        $track = new Track();

        $this->assertNull($trackSource->getId());

        $result = $trackSource->setTrack($track);
        $this->assertSame($trackSource, $result);
        $this->assertSame($track, $trackSource->getTrack());

        $result = $trackSource->setPlatform(TrackSource::PLATFORM_SPOTIFY);
        $this->assertSame($trackSource, $result);
        $this->assertSame(TrackSource::PLATFORM_SPOTIFY, $trackSource->getPlatform());

        $result = $trackSource->setPlatformTrackId('spotify-track-123');
        $this->assertSame($trackSource, $result);
        $this->assertSame('spotify-track-123', $trackSource->getPlatformTrackId());

        $metadata = ['popularity' => 85, 'explicit' => false];
        $result = $trackSource->setMetadata($metadata);
        $this->assertSame($trackSource, $result);
        $this->assertSame($metadata, $trackSource->getMetadata());
    }

    public function testSetPlatformWithValidPlatforms(): void
    {
        $trackSource = new TrackSource();

        $trackSource->setPlatform(TrackSource::PLATFORM_SPOTIFY);
        $this->assertSame(TrackSource::PLATFORM_SPOTIFY, $trackSource->getPlatform());

        $trackSource->setPlatform(TrackSource::PLATFORM_DEEZER);
        $this->assertSame(TrackSource::PLATFORM_DEEZER, $trackSource->getPlatform());

        $trackSource->setPlatform(TrackSource::PLATFORM_SOUNDCLOUD);
        $this->assertSame(TrackSource::PLATFORM_SOUNDCLOUD, $trackSource->getPlatform());

        $trackSource->setPlatform(TrackSource::PLATFORM_APPLE_MUSIC);
        $this->assertSame(TrackSource::PLATFORM_APPLE_MUSIC, $trackSource->getPlatform());
    }

    public function testSetPlatformWithInvalidPlatformThrowsException(): void
    {
        $trackSource = new TrackSource();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid platform "invalid". Allowed platforms: spotify, deezer, soundcloud, apple_music');

        $trackSource->setPlatform('invalid');
    }

    public function testPlatformConstants(): void
    {
        $this->assertSame('spotify', TrackSource::PLATFORM_SPOTIFY);
        $this->assertSame('deezer', TrackSource::PLATFORM_DEEZER);
        $this->assertSame('soundcloud', TrackSource::PLATFORM_SOUNDCLOUD);
        $this->assertSame('apple_music', TrackSource::PLATFORM_APPLE_MUSIC);

        $this->assertCount(4, TrackSource::PLATFORMS);
        $this->assertContains(TrackSource::PLATFORM_SPOTIFY, TrackSource::PLATFORMS);
        $this->assertContains(TrackSource::PLATFORM_DEEZER, TrackSource::PLATFORMS);
        $this->assertContains(TrackSource::PLATFORM_SOUNDCLOUD, TrackSource::PLATFORMS);
        $this->assertContains(TrackSource::PLATFORM_APPLE_MUSIC, TrackSource::PLATFORMS);
    }

    public function testTimeStampableTrait(): void
    {
        $trackSource = new TrackSource();
        $trackSource->setCreatedAt();

        $this->assertInstanceOf(\DateTimeImmutable::class, $trackSource->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $trackSource->getUpdatedAt());
    }

    public function testDefaultMetadata(): void
    {
        $trackSource = new TrackSource();
        $this->assertSame([], $trackSource->getMetadata());
    }
}
