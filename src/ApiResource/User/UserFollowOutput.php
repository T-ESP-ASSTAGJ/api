<?php

declare(strict_types=1);

namespace App\ApiResource\User;

final readonly class UserFollowOutput
{
    public function __construct(
        public ?int $id,
        public ?string $username,
        public ?string $profilePicture,
    ) {
    }
}
