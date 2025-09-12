<?php

declare(strict_types=1);

namespace App\DTO\Spotify;

class TrackDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly array $artists,
        public readonly ?string $albumId,
        public readonly ?string $albumName,
        public readonly int $durationMs,
        public readonly int $popularity,
        public readonly ?string $previewUrl,
        public readonly ?string $imageUrl,
        public readonly string $externalUrl
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            artists: array_map(fn($artist) => [
                'id' => $artist['id'],
                'name' => $artist['name']
            ], $data['artists'] ?? []),
            albumId: $data['album']['id'] ?? null,
            albumName: $data['album']['name'] ?? null,
            durationMs: $data['duration_ms'] ?? 0,
            popularity: $data['popularity'] ?? 0,
            previewUrl: $data['preview_url'] ?? null,
            imageUrl: !empty($data['album']['images']) ? $data['album']['images'][0]['url'] : null,
            externalUrl: $data['external_urls']['spotify'] ?? ''
        );
    }
}