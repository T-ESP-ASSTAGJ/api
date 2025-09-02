<?php

declare(strict_types=1);

namespace App\DTO\AppleMusic;

class SongDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $artistName,
        public readonly string $albumName,
        public readonly int $durationMs,
        public readonly ?string $artworkUrl,
        public readonly array $genres,
        public readonly string $isrc
    ) {}

    public static function fromArray(array $data): self
    {
        $attributes = $data['attributes'] ?? [];
        
        return new self(
            id: $data['id'] ?? '',
            name: $attributes['name'] ?? '',
            artistName: $attributes['artistName'] ?? '',
            albumName: $attributes['albumName'] ?? '',
            durationMs: $attributes['durationInMillis'] ?? 0,
            artworkUrl: isset($attributes['artwork']['url']) ? 
                str_replace(['{w}', '{h}'], ['400', '400'], $attributes['artwork']['url']) : null,
            genres: array_map(fn($genre) => $genre['name'], $attributes['genreNames'] ?? []),
            isrc: $attributes['isrc'] ?? ''
        );
    }
}