<?php

declare(strict_types=1);

namespace App\ApiResource\SoundCloud;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\State\SoundCloud\UserProvider;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/soundcloud/users/{id}',
            shortName: 'SoundCloudUser',
            provider: UserProvider::class
        ),
    ],
)]
readonly class UserDTO
{
    public function __construct(
        #[ApiProperty(identifier: true)]
        public int $id,
        public string $username,
        public string $permalink,
        public string $permalink_url,
        public ?string $avatar_url,
        public ?string $country,
        public ?string $city,
        public ?string $description,
        public int $followers_count,
        public int $followings_count,
        public int $track_count,
        public int $playlist_count,
    ) {
    }
}