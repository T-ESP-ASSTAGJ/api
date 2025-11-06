<?php

declare(strict_types=1);

namespace App\DTO\Artist;

use ApiPlatform\Metadata\ApiProperty;

class ArtistGetOutput
{
    #[ApiProperty(example: 1)]
    public int $id;

    #[ApiProperty(example: 'The Weeknd')]
    public string $name;

    /**
     * @var array<string, mixed>
     */
    #[ApiProperty(example: [
        'genre' => 'R&B/Pop',
        'country' => 'Canada',
        'formed_year' => 2009,
        'spotify_id' => '1Xyo4u8uXC1ZmMpatF05PJ',
        'external_urls' => [
            'spotify' => 'https://open.spotify.com/artist/1Xyo4u8uXC1ZmMpatF05PJ',
            'apple_music' => 'https://music.apple.com/artist/the-weeknd/479756766'
        ],
        'followers' => 90000000,
        'popularity' => 95,
        'image_url' => 'https://i.scdn.co/image/ab6761610000e5ebf6c5e4db6ab120dd6ddacb83'
    ])]
    public array $metadata;
}