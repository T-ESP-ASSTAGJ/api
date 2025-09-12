<?php

declare(strict_types=1);

namespace App\DTO\Spotify;

class ArtistDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly array $genres,
        public readonly ?string $imageUrl,
        public readonly string $externalUrl,
        public readonly int $followers,
        public readonly int $popularity
    ) {}
}