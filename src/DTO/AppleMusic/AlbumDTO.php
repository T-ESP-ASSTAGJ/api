<?php

declare(strict_types=1);

namespace App\DTO\AppleMusic;

class AlbumDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $artistName,
        public readonly int $trackCount,
        public readonly ?string $artworkUrl,
        public readonly array $genres,
        public readonly string $releaseDate
    ) {}

    public static function fromArray(array $data): self
    {
        $attributes = $data['attributes'] ?? [];
        
        return new self(
            id: $data['id'] ?? '',
            name: $attributes['name'] ?? '',
            artistName: $attributes['artistName'] ?? '',
            trackCount: $attributes['trackCount'] ?? 0,
            artworkUrl: isset($attributes['artwork']['url']) ? 
                str_replace(['{w}', '{h}'], ['400', '400'], $attributes['artwork']['url']) : null,
            genres: array_map(fn($genre) => $genre['name'], $attributes['genreNames'] ?? []),
            releaseDate: $attributes['releaseDate'] ?? ''
        );
    }
}