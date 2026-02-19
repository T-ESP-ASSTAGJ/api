<?php

declare(strict_types=1);

namespace App\ApiResource\Conversation;

use ApiPlatform\Metadata\ApiProperty;
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
        #[Assert\NotBlank]
        #[ApiProperty(
            openapiContext: [
                'type' => 'array',
                'items' => ['type' => 'integer']
            ]
        )]
        public ?array $participants = null,
    ) {
    }
}
