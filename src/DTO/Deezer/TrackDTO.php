<?php

declare(strict_types=1);

namespace App\DTO\Deezer;

class TrackDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $artistName,
        public readonly string $artistId,
        public readonly string $albumTitle,
        public readonly string $albumId,
        public readonly int $duration,
        public readonly ?string $previewUrl,
        public readonly string $link
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (string)$data['id'],
            title: $data['title'] ?? '',
            artistName: $data['artist']['name'] ?? '',
            artistId: (string)($data['artist']['id'] ?? ''),
            albumTitle: $data['album']['title'] ?? '',
            albumId: (string)($data['album']['id'] ?? ''),
            duration: $data['duration'] ?? 0,
            previewUrl: $data['preview'] ?? null,
            link: $data['link'] ?? ''
        );
    }
}