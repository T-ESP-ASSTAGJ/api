<?php

declare(strict_types=1);

namespace App\DTO\Deezer;

class UserProfileDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $email,
        public readonly string $country,
        public readonly ?string $pictureUrl,
        public readonly string $link
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (string)$data['id'],
            name: $data['name'] ?? '',
            email: $data['email'] ?? null,
            country: $data['country'] ?? '',
            pictureUrl: $data['picture_medium'] ?? null,
            link: $data['link'] ?? ''
        );
    }
}