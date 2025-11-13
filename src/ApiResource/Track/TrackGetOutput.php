<?php

declare(strict_types=1);

namespace App\ApiResource\Track;

use ApiPlatform\Metadata\ApiProperty;

class TrackGetOutput
{
    #[ApiProperty(example: 1)]
    public int $id;

    #[ApiProperty(example: 'Blinding Lights')]
    public string $title;

    #[ApiProperty(example: 'https://i.scdn.co/image/ab67616d0000b273123456789abcdef')]
    public ?string $coverUrl;

    /**
     * @var array<string, mixed>
     */
    #[ApiProperty(example: [
        'album' => 'After Hours',
        'duration' => 200,
        'genre' => 'Pop',
        'platform' => 'spotify',
        'platform_id' => '4uLU6hMCjMI75M1A2tKUQC',
        'external_url' => 'https://open.spotify.com/track/4uLU6hMCjMI75M1A2tKUQC',
        'isrc' => 'USUG12000193',
        'preview_url' => 'https://p.scdn.co/mp3-preview/9af4e9e0b1e9e2e8e3e4e5e6e7e8e9f0',
    ])]
    public array $metadata;

    #[ApiProperty(example: 1)]
    public int $artistId;

    #[ApiProperty(example: 200)]
    public int $length;

    #[ApiProperty(example: '2025-08-25T10:30:00Z')]
    public ?\DateTimeInterface $createdAt;

    #[ApiProperty(example: '2025-08-25T15:45:00Z')]
    public ?\DateTimeInterface $updatedAt;
}
