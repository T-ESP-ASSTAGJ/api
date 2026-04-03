<?php

declare(strict_types=1);

namespace App\ApiResource\Message;

use App\ApiResource\Track\TrackInput;
use App\Entity\Enum\MessageTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @codeCoverageIgnore
 */
final readonly class MessageCreateInput
{
    public function __construct(
        #[Assert\NotNull]
        public int $conversationId,
        #[Assert\NotNull]
        public MessageTypeEnum $type,
        public ?string $content = null,
        #[Assert\NotNull(message: 'A music message must include track data.', groups: ['music'])]
        #[Assert\Valid]
        public ?TrackInput $track = null,
    ) {
    }
}
