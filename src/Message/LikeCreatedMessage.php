<?php

declare(strict_types=1);

namespace App\Message;
readonly class LikeCreatedMessage
{
    public function __construct(
        public int $likeId,
    ) {}
}
