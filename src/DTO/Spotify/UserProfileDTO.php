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
}