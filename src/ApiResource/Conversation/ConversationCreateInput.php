<?php

declare(strict_types=1);

namespace App\ApiResource\Conversation;

use Symfony\Component\Validator\Constraints as Assert;

readonly class ConversationCreateInput
{
    public function __construct(
        #[Assert\Type('boolean')]
        public bool $isGroup = false,

        #[Assert\Length(max: 255)]
        public ?string $groupName = null,

        /**
         * @var int[]
         */
        #[Assert\Type('array')]
        #[Assert\All([
            new Assert\Type('integer'),
        ])]
        public array $participants = [],
    ) {
    }
}
