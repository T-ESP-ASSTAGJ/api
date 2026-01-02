<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Track;

use App\ApiResource\Track\TrackSourceDto;
use PHPUnit\Framework\TestCase;

class TrackSourceDtoTest extends TestCase
{
    public function testConstructorWithAllParameters(): void
    {
        $metadata = ['popularity' => 85, 'explicit' => false];
        $dto = new TrackSourceDto('spotify', 'track_123', $metadata);

        $this->assertSame('spotify', $dto->platform);
        $this->assertSame('track_123', $dto->platformTrackId);
        $this->assertSame($metadata, $dto->metadata);
    }

    public function testConstructorWithDefaultValues(): void
    {
        $dto = new TrackSourceDto();

        $this->assertSame('', $dto->platform);
        $this->assertSame('', $dto->platformTrackId);
        $this->assertSame([], $dto->metadata);
    }

    public function testCanSetProperties(): void
    {
        $dto = new TrackSourceDto();
        $dto->platform = 'deezer';
        $dto->platformTrackId = 'deezer_track_456';
        $dto->metadata = ['rank' => 1];

        $this->assertSame('deezer', $dto->platform);
        $this->assertSame('deezer_track_456', $dto->platformTrackId);
        $this->assertSame(['rank' => 1], $dto->metadata);
    }

    public function testSupportsAllPlatforms(): void
    {
        $dto1 = new TrackSourceDto('spotify', 'id1');
        $this->assertSame('spotify', $dto1->platform);

        $dto2 = new TrackSourceDto('deezer', 'id2');
        $this->assertSame('deezer', $dto2->platform);

        $dto3 = new TrackSourceDto('soundcloud', 'id3');
        $this->assertSame('soundcloud', $dto3->platform);

        $dto4 = new TrackSourceDto('apple_music', 'id4');
        $this->assertSame('apple_music', $dto4->platform);
    }

    public function testMetadataDefaultIsEmptyArray(): void
    {
        $dto = new TrackSourceDto('spotify', 'id');

        $this->assertIsArray($dto->metadata);
        $this->assertCount(0, $dto->metadata);
    }
}
