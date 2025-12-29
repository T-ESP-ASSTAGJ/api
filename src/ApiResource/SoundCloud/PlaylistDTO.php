<?php

declare(strict_types=1);

namespace App\ApiResource\SoundCloud;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\State\SoundCloud\PlaylistProvider;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/soundcloud/playlists',
            shortName: 'SoundCloudPlaylist',
            provider: PlaylistProvider::class
        ),
        new Get(
            uriTemplate: '/soundcloud/playlists/{id}',
            shortName: 'SoundCloudPlaylist',
            provider: PlaylistProvider::class
        ),
    ],
)]
readonly class PlaylistDTO
{
    public function __construct(
        #[ApiProperty(identifier: true)]
        public int $id,
        public string $title,
        public ?string $description,
        public int $duration,
        public string $permalink_url,
        public ?string $artwork_url,
        public UserDTO $user,
        public string $created_at,
        public int $track_count,
        public ?array $tracks,
        public bool $is_public,
        public ?string $genre,
        public ?string $tag_list,
    ) {
    }
}