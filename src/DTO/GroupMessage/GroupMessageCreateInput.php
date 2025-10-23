<?php

declare(strict_types=1);

namespace App\DTO\GroupMessage;

use Symfony\Component\Validator\Constraints as Assert;

readonly class GroupMessageCreateInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        public int $groupId,

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
