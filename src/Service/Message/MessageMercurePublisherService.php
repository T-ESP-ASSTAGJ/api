<?php

declare(strict_types=1);

namespace App\Service\Message;

use App\ApiResource\Message\MercureMessageOutput;
use App\Entity\Enum\MercureTypeEnum;
use App\Entity\Message;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class MessageMercurePublisherService
{
    public function __construct(
        private HubInterface $hub,
        private SerializerInterface $serializer,
    ) {
    }

    public function publish(Message $message): void
    {
        $payload = $this->serializer->serialize(
            new MercureMessageOutput(MercureTypeEnum::Message, $message),
            'json',
            ['groups' => Message::SERIALIZATION_GROUP_MERCURE],
        );

        $this->hub->publish(new Update(
            $message->getConversation()->getMercureTopic(),
            $payload,
        ));
    }
}
