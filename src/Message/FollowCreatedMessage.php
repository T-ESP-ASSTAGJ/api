<?php

declare(strict_types=1);

namespace App\Message;

use App\Entity\User;

readonly class FollowCreatedMessage
{
    public function __construct(
        public User $currentUser,
        public User $userToFollow,
    ) {
    }
}
