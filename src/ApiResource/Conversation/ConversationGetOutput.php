<?php

declare(strict_types=1);

namespace App\ApiResource\Conversation;

readonly class ConversationGetOutput
{
    public function __construct(
        public int $id,
        public string $type,
        /**
         * @var array{
         *     id: int,
         *     username: string|null,
         *     profile_picture: string|null
         * }|null
         */
        public ?array $participant,
        /**
         * @var array{
         *     id: int,
         *     type: string,
         *     content: string|null,
         *     preview: string|null,
         *     author: array{
         *         id: int,
         *         username: string|null
         *     },
         *     created_at: string
         * }|null
         */
        public ?array $lastMessage,
        public int $unreadCount,
        public string $updatedAt,
    ) {
    }
}
