<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Spotify;

use App\ApiResource\Spotify\TrackDTO;
use PHPUnit\Framework\TestCase;

class TrackDTOTest extends TestCase
{
    public function testConstructor(): void
    {
        $track = new TrackDTO(
            id: 'spotify-track-123',
            name: 'Test Track',
            artists: ['Artist 1', 'Artist 2'],
            albumId: 'album-123',
            albumName: 'Test Album',
            durationMs: 180000,
            popularity: 85,
            previewUrl: 'https://example.com/preview.mp3',
            imageUrl: 'https://example.com/track.jpg',
            externalUrl: 'https://open.spotify.com/track/123'
        );

        $this->assertSame('spotify-track-123', $track->id);
        $this->assertSame('Test Track', $track->name);
        $this->assertSame(['Artist 1', 'Artist 2'], $track->artists);
        $this->assertSame('album-123', $track->albumId);
        $this->assertSame('Test Album', $track->albumName);
        $this->assertSame(180000, $track->durationMs);
        $this->assertSame(85, $track->popularity);
        $this->assertSame('https://example.com/preview.mp3', $track->previewUrl);
        $this->assertSame('https://example.com/track.jpg', $track->imageUrl);
        $this->assertSame('https://open.spotify.com/track/123', $track->externalUrl);
    }

    public function testConstructorWithNullableValues(): void
    {
        $track = new TrackDTO(
            id: 'spotify-track-456',
            name: 'Another Track',
            artists: [],
            albumId: null,
            albumName: null,
            durationMs: 0,
            popularity: 0,
            previewUrl: null,
            imageUrl: null,
            externalUrl: 'https://open.spotify.com/track/456'
        );

        $this->assertSame('spotify-track-456', $track->id);
        $this->assertSame('Another Track', $track->name);
        $this->assertSame([], $track->artists);
        $this->assertNull($track->albumId);
        $this->assertNull($track->albumName);
        $this->assertSame(0, $track->durationMs);
        $this->assertSame(0, $track->popularity);
        $this->assertNull($track->previewUrl);
        $this->assertNull($track->imageUrl);
        $this->assertSame('https://open.spotify.com/track/456', $track->externalUrl);
    }

    public function testReadonlyProperties(): void
    {
        $track = new TrackDTO(
            id: 'test',
            name: 'test',
            artists: [],
            albumId: 'album',
            albumName: 'album',
            durationMs: 100,
            popularity: 50,
            previewUrl: null,
            imageUrl: null,
            externalUrl: 'https://example.com'
        );

        $reflection = new \ReflectionClass($track);
        $this->assertTrue($reflection->isReadOnly());
    }
}
