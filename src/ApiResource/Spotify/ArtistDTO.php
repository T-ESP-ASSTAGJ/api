<?php

declare(strict_types=1);

namespace App\ApiResource\Spotify;

readonly class ArtistDTO
{
    public function __construct(
        public string $id,
        public string $name,
        /** @var array<string> */
        public array $genres,
        public ?string $imageUrl,
        public string $externalUrl,
        public int $followers,
        public int $popularity,
    ) {
    }
}
