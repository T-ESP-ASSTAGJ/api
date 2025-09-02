<?php

declare(strict_types=1);

namespace App\DTO\AppleMusic;

class PlaylistDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $artworkUrl,
        public readonly int $trackCount,
        public readonly bool $canEdit
    ) {}

    public static function fromArray(array $data): self
    {
        $attributes = $data['attributes'] ?? [];
        
        return new self(
            id: $data['id'] ?? '',
            name: $attributes['name'] ?? '',
            description: $attributes['description']['standard'] ?? null,
            artworkUrl: isset($attributes['artwork']['url']) ? 
                str_replace(['{w}', '{h}'], ['400', '400'], $attributes['artwork']['url']) : null,
            trackCount: $attributes['trackCount'] ?? 0,
            canEdit: $attributes['canEdit'] ?? false
        );
    }
}