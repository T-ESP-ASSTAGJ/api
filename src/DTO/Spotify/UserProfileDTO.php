<?php

declare(strict_types=1);

namespace App\DTO\Spotify;

class UserProfileDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $displayName,
        public readonly ?string $email,
        public readonly string $country,
        public readonly int $followers,
        public readonly ?string $imageUrl,
        public readonly string $product
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            displayName: $data['display_name'] ?? '',
            email: $data['email'] ?? null,
            country: $data['country'] ?? '',
            followers: $data['followers']['total'] ?? 0,
            imageUrl: !empty($data['images']) ? $data['images'][0]['url'] : null,
            product: $data['product'] ?? 'free'
        );
    }
}