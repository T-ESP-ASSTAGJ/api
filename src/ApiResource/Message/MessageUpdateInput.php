<?php

declare(strict_types=1);

namespace App\ApiResource\Message;

use ApiPlatform\Metadata\ApiProperty;
use App\Entity\Message;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class MessageUpdateInput
{
    #[ApiProperty(
        description: 'The new content of the message',
        example: 'Hello, this is my updated message!',
    )]
    #[Groups([Message::SERIALIZATION_GROUP_UPDATE])]
    #[Assert\Length(max: 1000)]
    public ?string $content = null;
}
