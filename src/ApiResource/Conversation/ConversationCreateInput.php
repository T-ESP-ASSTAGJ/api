<?php

declare(strict_types=1);

namespace App\ApiResource\Conversation;

use Symfony\Component\Validator\Constraints as Assert;

readonly class ConversationCreateInput
{
    public function __construct(
        #[Assert\NotNull]
        public bool $isGroup = false,

        public ?string $groupName = null,

        /**
         * @var array<int>|null Array of user IDs
         */
        #[Assert\Type('array')]
        public ?array $participants = null,
    ) {
    }
}
