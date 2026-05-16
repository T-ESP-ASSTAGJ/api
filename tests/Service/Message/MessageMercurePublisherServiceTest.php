<?php

declare(strict_types=1);

namespace App\Tests\Service\Message;

use App\ApiResource\Message\MercureMessageOutput;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Service\Message\MessageMercurePublisherService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\SerializerInterface;

class MessageMercurePublisherServiceTest extends TestCase
{
    public function testPublishSerializesAndPublishesToHub(): void
    {
        $conversation = $this->createMock(Conversation::class);
        $conversation->method('getMercureTopic')->willReturn('https://example.com/conversations/1');

        $message = $this->createMock(Message::class);
        $message->method('getConversation')->willReturn($conversation);

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->once())
            ->method('serialize')
            ->with(
                $this->isInstanceOf(MercureMessageOutput::class),
                'json',
                ['groups' => Message::SERIALIZATION_GROUP_MERCURE],
            )
            ->willReturn('{"type":"message"}')
        ;

        $hub = $this->createMock(HubInterface::class);
        $hub->expects($this->once())
            ->method('publish')
            ->with($this->callback(static function (Update $update): bool {
                return $update->getTopics() === ['https://example.com/conversations/1']
                    && '{"type":"message"}' === $update->getData();
            }))
        ;

        $service = new MessageMercurePublisherService($hub, $serializer);
        $service->publish($message);
    }
}
