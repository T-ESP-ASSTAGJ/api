<?php

declare(strict_types=1);

namespace App\ApiResource\Message;

use App\ApiResource\Track\TrackInput;
use App\Entity\Enum\MessageTypeEnum;
use App\Validator\Constraints\Base64;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

#[Assert\GroupSequenceProvider]
final readonly class MessageCreateInput implements GroupSequenceProviderInterface
{
    public function __construct(
        #[Assert\NotNull]
        public int $conversationId,
        #[Assert\NotNull]
        public MessageTypeEnum $type,
        #[Base64(groups: [MessageTypeEnum::Image->value])]
        #[Assert\NotBlank(groups: [MessageTypeEnum::Share->value])]
        #[Assert\Url(groups: [MessageTypeEnum::Share->value])]
        #[Assert\Length(max: 1000, groups: [MessageTypeEnum::Text->value])]
        public ?string $content = null,
        #[Assert\NotNull(groups: [MessageTypeEnum::Music->value])]
        #[Assert\Valid]
        public ?TrackInput $track = null,
    ) {
    }

    /**
     * @return string[]
     */
    public function getGroupSequence(): array
    {
        $groups = [$this::class];
        $groups[] = $this->type->value;

        return $groups;
    }
}
