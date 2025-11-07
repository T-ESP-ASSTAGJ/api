<?php

declare(strict_types=1);

namespace App\ApiResource\Conversation;

readonly class ConversationDetailOutput
{
    public function __construct(
        public int $id,
        public bool $isGroup,
        public ?string $groupName,
        public string $createdAt,
        /**
         * @var array<array{
         *     user_id: int,
         *     username: string|null,
         *     profile_picture: string|null,
         *     joined_at: string,
         *     left_at: string|null
         * }>
         */
        public array $participants,
    ) {
    }
}
