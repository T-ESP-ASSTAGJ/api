<?php

declare(strict_types=1);

namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
final readonly class PersistPostViewsMessage
{
    public function __construct(
        private int $postId,
    ) {
    }

    public function getPostId(): int
    {
        return $this->postId;
    }
}
