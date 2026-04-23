<?php

declare(strict_types=1);

namespace App\Message;

readonly class CommentCreatedMessage
{
    public function __construct(
        public int $postId,
        public int $userId,
    ) {
    }
}
