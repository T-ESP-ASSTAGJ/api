<?php

declare(strict_types=1);

namespace App\DTO\Spotify;

class PlaylistDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly bool $public,
        public readonly int $totalTracks,
        public readonly ?string $imageUrl,
        public readonly string $externalUrl,
        public readonly array $owner
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            description: $data['description'],
            public: $data['public'] ?? false,
            totalTracks: $data['tracks']['total'] ?? 0,
            imageUrl: !empty($data['images']) ? $data['images'][0]['url'] : null,
            externalUrl: $data['external_urls']['spotify'] ?? '',
            owner: [
                'id' => $data['owner']['id'] ?? '',
                'display_name' => $data['owner']['display_name'] ?? ''
            ]
        );
    }
}