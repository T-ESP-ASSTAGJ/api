<?php

declare(strict_types=1);

namespace App\Message;

readonly class MessageCreatedMessage
{
    public function __construct(
        public int $conversationId,
        public int $senderId,
        public int $messageId,
    ) {
    }
}
