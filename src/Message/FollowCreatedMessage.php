<?php

declare(strict_types=1);

namespace App\Message;

readonly class FollowCreatedMessage
{
    public function __construct(
        public int $currentUserId,
        public int $userToFollowId,
    ) {
    }
}
