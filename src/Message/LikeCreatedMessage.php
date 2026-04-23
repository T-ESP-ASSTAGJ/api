<?php

declare(strict_types=1);

namespace App\Message;

readonly class LikeCreatedMessage
{
    public function __construct(
        public int $userId,
        public int $ownerId,
        public int $likeId,
        public int $postId,
    ) {
    }
}
