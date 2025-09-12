<?php

declare(strict_types=1);

namespace App\DTO\Spotify;

readonly class AlbumDTO
{
    public function __construct(
        public string $id,
        public string $name,
        /** @var array<string> */
        public array $artists,
        public ?string $albumType,
        public int $totalTracks,
        public ?string $releaseDate,
        public ?string $imageUrl,
        public string $externalUrl,
        /** @var array<string> */
        public array $genres,
        public int $popularity,
    ) {
    }
}
