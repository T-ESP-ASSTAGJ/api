<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Message;

use App\ApiResource\Message\MercureMessageOutput;
use App\Entity\Conversation;
use App\Entity\Enum\MercureTypeEnum;
use App\Entity\Message;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class MercureMessageOutputTest extends TestCase
{
    private function createMessageWithDependencies(): Message
    {
        $user = new User();
        $reflection = new \ReflectionClass($user);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($user, 42);

        $emailProperty = $reflection->getProperty('email');
        $emailProperty->setAccessible(true);
        $emailProperty->setValue($user, 'sergio@test.com');

        $user->setUsername('sergio');
        $user->setProfilePicture('https://example.com/pic.jpg');

        $conversation = new Conversation();
        $convReflection = new \ReflectionClass($conversation);
        $convIdProperty = $convReflection->getProperty('id');
        $convIdProperty->setAccessible(true);
        $convIdProperty->setValue($conversation, 7);

        $message = new Message();
        $msgReflection = new \ReflectionClass($message);
        $msgIdProperty = $msgReflection->getProperty('id');
        $msgIdProperty->setAccessible(true);
        $msgIdProperty->setValue($message, 99);

        $message->setAuthor($user);
        $message->setConversation($conversation);
        $message->setType(Message::TYPE_TEXT);
        $message->setContent('Hello world!');
        $message->setCreatedAt();

        return $message;
    }

    public function testConstructor(): void
    {
        $message = $this->createMessageWithDependencies();
        $output = new MercureMessageOutput(MercureTypeEnum::Message, $message);

        $this->assertInstanceOf(MercureMessageOutput::class, $output);
    }

    public function testToJsonReturnsValidJson(): void
    {
        $message = $this->createMessageWithDependencies();
        $output = new MercureMessageOutput(MercureTypeEnum::Message, $message);

        $json = $output->toJson();

        $this->assertJson($json);
    }

    public function testToJsonContainsExpectedFields(): void
    {
        $message = $this->createMessageWithDependencies();
        $output = new MercureMessageOutput(MercureTypeEnum::Message, $message);

        $data = json_decode($output->toJson(), true);

        $this->assertSame('message', $data['type']);
        $this->assertSame(99, $data['message']['id']);
        $this->assertSame(7, $data['message']['conversationId']);
        $this->assertSame(42, $data['message']['author']['id']);
        $this->assertSame('sergio', $data['message']['author']['username']);
        $this->assertSame('https://example.com/pic.jpg', $data['message']['author']['profilePicture']);
        $this->assertSame(Message::TYPE_TEXT, $data['message']['type']);
        $this->assertSame('Hello world!', $data['message']['content']);
        $this->assertArrayHasKey('createdAt', $data['message']);
    }

    public function testToJsonCreatedAtIsFormatted(): void
    {
        $user = new User();
        $reflection = new \ReflectionClass($user);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($user, 1);

        $emailProperty = $reflection->getProperty('email');
        $emailProperty->setAccessible(true);
        $emailProperty->setValue($user, 'test@test.com');

        $conversation = new Conversation();
        $convReflection = new \ReflectionClass($conversation);
        $convIdProperty = $convReflection->getProperty('id');
        $convIdProperty->setAccessible(true);
        $convIdProperty->setValue($conversation, 1);

        $message = new Message();
        $message->setAuthor($user);
        $message->setConversation($conversation);
        $message->setContent('test');
        $message->setCreatedAt();

        $output = new MercureMessageOutput(MercureTypeEnum::Message, $message);
        $data = json_decode($output->toJson(), true);

        $this->assertNotNull($data['message']['createdAt']);
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}$/',
            $data['message']['createdAt']
        );
    }
}
