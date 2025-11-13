<?php

declare(strict_types=1);

namespace App\ApiResource\Post;

use ApiPlatform\Metadata\ApiProperty;
use App\ApiResource\Track\TrackGetOutput;

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

    #[ApiProperty(example: 1)]
    public int $trackId;

    public TrackGetOutput $track;

    #[ApiProperty(example: 'https://example.com/photos/sunset-beach.jpg')]
    public ?string $photoUrl;

    #[ApiProperty(example: 'Paris, France')]
    public ?string $location;

    #[ApiProperty(example: '2025-08-25T10:30:00Z')]
    public ?\DateTimeInterface $createdAt;

    #[ApiProperty(example: '2025-08-25T15:45:00Z')]
    public ?\DateTimeInterface $updatedAt;
}
