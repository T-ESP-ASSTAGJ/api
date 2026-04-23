<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Message;

use App\ApiResource\Message\MercureMessageOutput;
use App\Entity\Conversation;
use App\Entity\Enum\MercureTypeEnum;
use App\Entity\Enum\MessageTypeEnum;
use App\Entity\Message;
use App\Entity\User;
use App\Util\ReflectionUtil;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class MercureMessageOutputTest extends TestCase
{
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());
        $normalizer = new ObjectNormalizer($classMetadataFactory);

        $this->serializer = new Serializer(
            [
                new DateTimeNormalizer(),
                new BackedEnumNormalizer(),
                $normalizer,
            ],
            [new JsonEncoder()],
        );
    }

    public function testSerializationMatchesExpectedStructure(): void
    {
        $message = $this->createMessageWithDependencies();
        $output = new MercureMessageOutput(MercureTypeEnum::Message, $message);

        $json = $this->serializer->serialize(
            $output,
            'json',
            ['groups' => [Message::SERIALIZATION_GROUP_MERCURE]],
        );

        $data = json_decode($json, true, 512, \JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('type', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertSame(MercureTypeEnum::Message->value, $data['type']);

        $msgData = $data['message'];
        $this->assertSame(99, $msgData['id']);
        $this->assertSame('Hello world!', $msgData['content']);

        $this->assertArrayHasKey('author', $msgData);
        $this->assertSame(42, $msgData['author']['id']);
        $this->assertSame('sergio', $msgData['author']['username']);
        $this->assertSame('https://example.com/pic.jpg', $msgData['author']['profilePicture']);
    }

    private function createMessageWithDependencies(): Message
    {
        $user = new User();
        ReflectionUtil::setPropertyValue($user, 'id', 42);
        $user->setUsername('sergio');
        $user->setProfilePicture('https://example.com/pic.jpg');

        $conversation = new Conversation();
        ReflectionUtil::setPropertyValue($conversation, 'id', 9);

        $message = new Message();
        ReflectionUtil::setPropertyValue($message, 'id', 99);
        $message->setAuthor($user);
        $message->setConversation($conversation);
        $message->setType(MessageTypeEnum::Text);
        $message->setContent('Hello world!');
        $message->setCreatedAt(new \DateTimeImmutable('2024-01-01 12:00:00'));

        return $message;
    }
}
