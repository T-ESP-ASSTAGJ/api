<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Spotify;

use App\ApiResource\Spotify\PlaylistDTO;
use PHPUnit\Framework\TestCase;

class PlaylistDTOTest extends TestCase
{
    public function testConstructor(): void
    {
        $playlist = new PlaylistDTO(
            id: 'spotify-playlist-123',
            name: 'Test Playlist',
            description: 'A test playlist',
            public: true,
            totalTracks: 50,
            imageUrl: 'https://example.com/playlist.jpg',
            externalUrl: 'https://open.spotify.com/playlist/123',
            owner: ['id' => 'user123', 'name' => 'Test User']
        );

        $this->assertSame('spotify-playlist-123', $playlist->id);
        $this->assertSame('Test Playlist', $playlist->name);
        $this->assertSame('A test playlist', $playlist->description);
        $this->assertTrue($playlist->public);
        $this->assertSame(50, $playlist->totalTracks);
        $this->assertSame('https://example.com/playlist.jpg', $playlist->imageUrl);
        $this->assertSame('https://open.spotify.com/playlist/123', $playlist->externalUrl);
        $this->assertSame(['id' => 'user123', 'name' => 'Test User'], $playlist->owner);
    }

    public function testConstructorWithNullableValues(): void
    {
        $playlist = new PlaylistDTO(
            id: 'spotify-playlist-456',
            name: 'Private Playlist',
            description: null,
            public: false,
            totalTracks: 0,
            imageUrl: null,
            externalUrl: 'https://open.spotify.com/playlist/456',
            owner: []
        );

        $this->assertSame('spotify-playlist-456', $playlist->id);
        $this->assertSame('Private Playlist', $playlist->name);
        $this->assertNull($playlist->description);
        $this->assertFalse($playlist->public);
        $this->assertSame(0, $playlist->totalTracks);
        $this->assertNull($playlist->imageUrl);
        $this->assertSame('https://open.spotify.com/playlist/456', $playlist->externalUrl);
        $this->assertSame([], $playlist->owner);
    }

    public function testReadonlyProperties(): void
    {
        $playlist = new PlaylistDTO(
            id: 'test',
            name: 'test',
            description: 'test',
            public: true,
            totalTracks: 1,
            imageUrl: null,
            externalUrl: 'https://example.com',
            owner: []
        );

        $reflection = new \ReflectionClass($playlist);
        $this->assertTrue($reflection->isReadOnly());
    }
}
