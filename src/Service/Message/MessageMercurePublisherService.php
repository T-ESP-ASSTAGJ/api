<?php

declare(strict_types=1);

namespace App\Service\Message;

use App\ApiResource\Message\MercureMessageOutput;
use App\Entity\Enum\MercureTypeEnum;
use App\Entity\Message;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Publie un nouveau message sur le topic Mercure de sa conversation pour une livraison en temps réel.
 *
 * Le contenu est sérialisé sous le groupe `message:mercure` et publié sur le topic
 * `/conversations/{id}`, auquel les clients s'abonnent pour les mises à jour en direct.
 */
readonly class MessageMercurePublisherService
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
