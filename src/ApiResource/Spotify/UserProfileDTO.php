<?php

declare(strict_types=1);

namespace App\ApiResource\Spotify;

readonly class UserProfileDTO
{
    public function __construct(
        public string $id,
        public string $displayName,
        public ?string $email,
        public string $country,
        public int $followers,
        public ?string $imageUrl,
        public string $product,
    ) {
    }
}
