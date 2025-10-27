<?php

declare(strict_types=1);

namespace App\ApiResource\Post;

use ApiPlatform\Metadata\ApiProperty;

class PostGetOutput
{
    #[ApiProperty(example: 1)]
    public int $id;

    #[ApiProperty(example: 42)]
    public int $userId;

    #[ApiProperty(example: 'https://p.scdn.co/mp3-preview/9af4e9e0b1e9e2e8e3e4e5e6e7e8e9f0')]
    public ?string $songPreviewUrl;

    #[ApiProperty(example: 'Amazing sunset vibes! 🌅 #music #vibes')]
    public ?string $caption;

    /**
     * @var array{
     *     title: string,
     *     artist: string,
     *     album: string,
     *     duration: int,
     *     genre: string,
     *     platform: string,
     *     platform_id: string,
     *     external_url: string,
     *     isrc: string
     * }
     */
    #[ApiProperty(example: [
        'title' => 'Blinding Lights',
        'artist' => 'The Weeknd',
        'album' => 'After Hours',
        'duration' => 200,
        'genre' => 'Pop',
        'platform' => 'spotify',
        'platform_id' => '4uLU6hMCjMI75M1A2tKUQC',
        'external_url' => 'https://open.spotify.com/track/4uLU6hMCjMI75M1A2tKUQC',
        'isrc' => 'USUG12000193',
    ])]
    public array $track;

    #[ApiProperty(example: 'https://example.com/photos/sunset-beach.jpg')]
    public ?string $photoUrl;

    #[ApiProperty(example: 'Paris, France')]
    public ?string $location;

    #[ApiProperty(example: '2025-08-25T10:30:00Z')]
    public ?\DateTimeInterface $createdAt;

    #[ApiProperty(example: '2025-08-25T15:45:00Z')]
    public ?\DateTimeInterface $updatedAt;
}
