<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Spotify;

use App\ApiResource\Spotify\AlbumDTO;
use PHPUnit\Framework\TestCase;

class AlbumDTOTest extends TestCase
{
    public function testConstructor(): void
    {
        $album = new AlbumDTO(
            id: 'spotify-album-123',
            name: 'Test Album',
            artists: ['Artist 1', 'Artist 2'],
            albumType: 'album',
            totalTracks: 12,
            releaseDate: '2025-01-01',
            imageUrl: 'https://example.com/album.jpg',
            externalUrl: 'https://open.spotify.com/album/123',
            genres: ['pop', 'rock'],
            popularity: 85
        );

        $this->assertSame('spotify-album-123', $album->id);
        $this->assertSame('Test Album', $album->name);
        $this->assertSame(['Artist 1', 'Artist 2'], $album->artists);
        $this->assertSame('album', $album->albumType);
        $this->assertSame(12, $album->totalTracks);
        $this->assertSame('2025-01-01', $album->releaseDate);
        $this->assertSame('https://example.com/album.jpg', $album->imageUrl);
        $this->assertSame('https://open.spotify.com/album/123', $album->externalUrl);
        $this->assertSame(['pop', 'rock'], $album->genres);
        $this->assertSame(85, $album->popularity);
    }

    public function testConstructorWithNullableValues(): void
    {
        $album = new AlbumDTO(
            id: 'spotify-album-456',
            name: 'Another Album',
            artists: [],
            albumType: null,
            totalTracks: 5,
            releaseDate: null,
            imageUrl: null,
            externalUrl: 'https://open.spotify.com/album/456',
            genres: [],
            popularity: 0
        );

        $this->assertSame('spotify-album-456', $album->id);
        $this->assertSame('Another Album', $album->name);
        $this->assertSame([], $album->artists);
        $this->assertNull($album->albumType);
        $this->assertSame(5, $album->totalTracks);
        $this->assertNull($album->releaseDate);
        $this->assertNull($album->imageUrl);
        $this->assertSame('https://open.spotify.com/album/456', $album->externalUrl);
        $this->assertSame([], $album->genres);
        $this->assertSame(0, $album->popularity);
    }

    public function testReadonlyProperties(): void
    {
        $album = new AlbumDTO(
            id: 'test',
            name: 'test',
            artists: [],
            albumType: 'album',
            totalTracks: 1,
            releaseDate: '2025-01-01',
            imageUrl: null,
            externalUrl: 'https://example.com',
            genres: [],
            popularity: 50
        );

        $reflection = new \ReflectionClass($album);
        $this->assertTrue($reflection->isReadOnly());
    }
}
