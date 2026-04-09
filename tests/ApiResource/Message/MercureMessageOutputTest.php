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
            [new JsonEncoder()]
        );
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
        $message->setCreatedAt();

        return $message;
    }

    public function testSerializationMatchesExpectedStructure(): void
    {
        $message = $this->createMessageWithDependencies();
        $output = new MercureMessageOutput(MercureTypeEnum::Message, $message);

        $json = $this->serializer->serialize(
            $output,
            'json',
            ['groups' => [Message::SERIALIZATION_GROUP_MERCURE]]
        );

        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(MercureTypeEnum::Message->value, $data['type']);
        $this->assertIsArray($data['message']);

        $this->assertSame(99, $data['message']['id']);
        $this->assertSame(9, $data['message']['conversationId']);
        $this->assertSame('Hello world!', $data['message']['content']);
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}$/',
            $data['message']['createdAt']
        );
        $this->assertSame(42, $data['message']['author']['id']);
        $this->assertSame('sergio', $data['message']['author']['username']);
        $this->assertArrayNotHasKey('updatedAt', $data['message']['author']);
    }
}
