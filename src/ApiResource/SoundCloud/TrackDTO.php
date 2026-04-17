<?php

declare(strict_types=1);

namespace App\ApiResource\SoundCloud;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use App\State\SoundCloud\TrackProvider;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/soundcloud/tracks/search',
            shortName: 'SoundCloud',
            provider: TrackProvider::class,
            parameters: [
                'q' => new QueryParameter(required: true),
                'isrc' => new QueryParameter(),
            ]),
        new Get(
            uriTemplate: '/soundcloud/tracks/{id}',
            shortName: 'SoundCloud',
            provider: TrackProvider::class
        ),
    ],
)]
readonly class TrackDTO
{
    public function __construct(
        #[ApiProperty(identifier: true)]
        public int $id,
        public string $title,
        public ?string $description,
        public int $duration,
        public string $permalink_url,
        public ?string $artwork_url,
        public int $playback_count,
        public int $likes_count,
        public bool $streamable,
        public ?string $download_url,
        public UserDTO $user,
        public string $created_at,
        public ?string $genre,
        public ?string $tag_list,
        public ?string $isrc,
    ) {
    }
}
