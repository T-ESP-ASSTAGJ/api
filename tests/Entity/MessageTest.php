<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use App\Entity\Enum\MessageTypeEnum;
use App\Entity\Message;
use App\Entity\Track;
use App\Entity\User;
use App\Util\ReflectionUtil;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $message = new Message();
        $conversation = new Conversation();
        $author = new User();

        $this->assertNull($message->getId());

        $result = $message->setConversation($conversation);
        $this->assertSame($message, $result);
        $this->assertSame($conversation, $message->getConversation());

        $result = $message->setAuthor($author);
        $this->assertSame($message, $result);
        $this->assertSame($author, $message->getAuthor());

        $result = $message->setType(MessageTypeEnum::Text);
        $this->assertSame($message, $result);
        $this->assertSame(MessageTypeEnum::Text, $message->getType());

        $result = $message->setContent('Hello, world!');
        $this->assertSame($message, $result);
        $this->assertSame('Hello, world!', $message->getContent());

        $track = new Track();
        $track->setArtistName('ArtistName');
        $track->setTitle('Song Title');
        $track->setCoverImage('https://example.com/cover.jpg');
        $track->setSongId('123');
        $track->setReleaseYear(1999);

        $result = $message->setTrack($track);
        $this->assertSame($message, $result);
        $this->assertSame($track, $message->getTrack());

        $track = new Track();
        $track->setArtistName('ArtistName');
        $track->setTitle('Song Title');
        $track->setCoverImage('https://example.com/cover.jpg');
        $track->setSongId('123');
        $track->setReleaseYear(1999);

        $result = $message->setTrack($track);
        $this->assertSame($message, $result);
        $this->assertSame($track, $message->getTrack());
    }

    public function testDefaultValues(): void
    {
        $message = new Message();

        $this->assertSame(MessageTypeEnum::Text, $message->getType());
        $this->assertNull($message->getContent());
        $this->assertNull($message->getTrack());
    }

    public function testIsMusicMessage(): void
    {
        $message = new Message();

        $message->setType(MessageTypeEnum::Text);
        $this->assertFalse($message->isMusicMessage());

        $message->setType(MessageTypeEnum::Music);
        $this->assertTrue($message->isMusicMessage());
    }

    public function testTypeConstants(): void
    {
        $this->assertSame('text', MessageTypeEnum::Text->value);
        $this->assertSame('music', MessageTypeEnum::Music->value);
    }

    public function testGetConversationId(): void
    {
        $message = new Message();
        $conversation = new Conversation();
        ReflectionUtil::setPropertyValue($conversation, 'id', 123);

        $message->setConversation($conversation);

        $this->assertSame(123, $message->getConversationId());
    }

    public function testGetMessagePreview(): void
    {
        $textMessage = new Message();
        $textMessage->setContent('text message');
        $textMessage->setType(MessageTypeEnum::Text);
        $this->assertSame($textMessage->getContent(), $textMessage->getMessagePreview());

        $musicMessage = new Message();
        $musicMessage->setContent('music message');
        $musicMessage->setType(MessageTypeEnum::Music);
        $this->assertSame('Vous a partagé une musique', $musicMessage->getMessagePreview());
    }

    public function testIsReadBy(): void
    {
        $user = new User();
        $conversation = new Conversation();
        $participant = new ConversationParticipant();
        $participant->setUser($user);

        $conversation->addParticipant($participant);

        $message = new Message();
        $message->setConversation($conversation);

        $readTime = new \DateTimeImmutable('2024-01-01 12:00:00');

        $this->assertFalse($message->isReadBy($user), 'Should be false if lastReadAt is null');

        $participant->setLastReadAt($readTime);
        $message->setCreatedAt($readTime->modify('-1 hour'));
        $this->assertTrue($message->isReadBy($user), 'Should be true if message is older than read date');

        $message->setCreatedAt($readTime->modify('+1 hour'));
        $this->assertFalse($message->isReadBy($user), 'Should be false if message is newer than read date');
    }
}
