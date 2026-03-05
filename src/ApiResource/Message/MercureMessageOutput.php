<?php

declare(strict_types=1);

namespace App\ApiResource\Message;

use App\Entity\Enum\MercureTypeEnum;
use App\Entity\Message;

class MercureMessageOutput
{
    private MercureTypeEnum $type;
    private Message $message;

    public function __construct(MercureTypeEnum $type, Message $message)
    {
        $this->type = $type;
        $this->message = $message;
    }

    public function toJson(): string
    {
        $author = $this->message->getAuthor();

        return json_encode([
            'type' => $this->type->value,
            'message' => [
                'id' => $this->message->getId(),
                'conversationId' => $this->message->getConversation()->getId(),
                'author' => [
                    'id' => $author->getId(),
                    'username' => $author->getUsername(),
                    'profilePicture' => $author->getProfilePicture(),
                ],
                'type' => $this->message->getType(),
                'content' => $this->message->getContent(),
                'createdAt' => $this->message->getCreatedAt()->format(\DateTimeInterface::ATOM),
            ],
        ]);
    }
}
