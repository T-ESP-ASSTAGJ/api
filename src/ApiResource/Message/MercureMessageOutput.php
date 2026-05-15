<?php

declare(strict_types=1);

namespace App\ApiResource\Message;

use App\Entity\Enum\MercureTypeEnum;
use App\Entity\Message;
use Symfony\Component\Serializer\Annotation\Groups;

final readonly class MercureMessageOutput
{
    public function __construct(
        #[Groups([Message::SERIALIZATION_GROUP_MERCURE])]
        public MercureTypeEnum $type,
        #[Groups([Message::SERIALIZATION_GROUP_MERCURE])]
        public Message $message,
    ) {
    }
}
