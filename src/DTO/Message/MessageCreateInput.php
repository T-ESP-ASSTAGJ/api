<?php

declare(strict_types=1);

namespace App\DTO\Message;

use Symfony\Component\Validator\Constraints as Assert;

readonly class MessageCreateInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        public int $conversationId,

        #[Assert\Choice(['text', 'music'])]
        public string $type = 'text',

        public ?string $content = null,

        /**
         * @var array{
         *     platform: string,
         *     track_id: string,
         *     fallback_ids: array<string, string>
         * }|null
         */
        public ?array $track = null,
    ) {
    }
}