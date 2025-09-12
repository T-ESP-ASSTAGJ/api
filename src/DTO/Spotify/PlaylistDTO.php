<?php

declare(strict_types=1);

namespace App\DTO\Spotify;

readonly class PlaylistDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $description,
        public bool $public,
        public int $totalTracks,
        public ?string $imageUrl,
        public string $externalUrl,
        /** @var array<string> */
        public array $owner,
    ) {
    }
}
