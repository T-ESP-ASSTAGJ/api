<?php

declare(strict_types=1);

namespace App\DTO\Spotify;

class AlbumDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly array $artists,
        public readonly ?string $albumType,
        public readonly int $totalTracks,
        public readonly ?string $releaseDate,
        public readonly ?string $imageUrl,
        public readonly string $externalUrl,
        public readonly array $genres,
        public readonly int $popularity
    ) {}
}