<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Spotify;

use App\ApiResource\Spotify\ArtistDTO;
use PHPUnit\Framework\TestCase;

class ArtistDTOTest extends TestCase
{
    public function testConstructor(): void
    {
        $artist = new ArtistDTO(
            id: 'spotify-artist-123',
            name: 'Test Artist',
            genres: ['pop', 'rock'],
            imageUrl: 'https://example.com/artist.jpg',
            externalUrl: 'https://open.spotify.com/artist/123',
            followers: 1000000,
            popularity: 90
        );

        $this->assertSame('spotify-artist-123', $artist->id);
        $this->assertSame('Test Artist', $artist->name);
        $this->assertSame(['pop', 'rock'], $artist->genres);
        $this->assertSame('https://example.com/artist.jpg', $artist->imageUrl);
        $this->assertSame('https://open.spotify.com/artist/123', $artist->externalUrl);
        $this->assertSame(1000000, $artist->followers);
        $this->assertSame(90, $artist->popularity);
    }

    public function testConstructorWithNullableValues(): void
    {
        $artist = new ArtistDTO(
            id: 'spotify-artist-456',
            name: 'Another Artist',
            genres: [],
            imageUrl: null,
            externalUrl: 'https://open.spotify.com/artist/456',
            followers: 0,
            popularity: 0
        );

        $this->assertSame('spotify-artist-456', $artist->id);
        $this->assertSame('Another Artist', $artist->name);
        $this->assertSame([], $artist->genres);
        $this->assertNull($artist->imageUrl);
        $this->assertSame('https://open.spotify.com/artist/456', $artist->externalUrl);
        $this->assertSame(0, $artist->followers);
        $this->assertSame(0, $artist->popularity);
    }

    public function testReadonlyProperties(): void
    {
        $artist = new ArtistDTO(
            id: 'test',
            name: 'test',
            genres: [],
            imageUrl: null,
            externalUrl: 'https://example.com',
            followers: 100,
            popularity: 50
        );

        $reflection = new \ReflectionClass($artist);
        $this->assertTrue($reflection->isReadOnly());
    }
}
