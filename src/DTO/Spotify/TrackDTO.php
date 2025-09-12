<?php

declare(strict_types=1);

namespace App\DTO\Spotify;

readonly class TrackDTO
{
    public function __construct(
        public string $id,
        public string $name,
        /** @var array<string> */
        public array $artists,
        public ?string $albumId,
        public ?string $albumName,
        public int $durationMs,
        public int $popularity,
        public ?string $previewUrl,
        public ?string $imageUrl,
        public string $externalUrl,
    ) {
    }
}
