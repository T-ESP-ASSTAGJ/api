<?php

declare(strict_types=1);

namespace App\DTO\SoundCloud;

class UserProfileDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $username,
        public readonly ?string $fullName,
        public readonly string $country,
        public readonly int $followersCount,
        public readonly int $followingsCount,
        public readonly int $publicPlaylistsCount,
        public readonly ?string $avatarUrl,
        public readonly string $permalink
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (string)$data['id'],
            username: $data['username'] ?? '',
            fullName: $data['full_name'] ?? null,
            country: $data['country'] ?? '',
            followersCount: $data['followers_count'] ?? 0,
            followingsCount: $data['followings_count'] ?? 0,
            publicPlaylistsCount: $data['public_favorites_count'] ?? 0,
            avatarUrl: $data['avatar_url'] ?? null,
            permalink: $data['permalink_url'] ?? ''
        );
    }
}