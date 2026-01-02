<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
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

        $result = $message->setType(Message::TYPE_TEXT);
        $this->assertSame($message, $result);
        $this->assertSame(Message::TYPE_TEXT, $message->getType());

        $result = $message->setContent('Hello, world!');
        $this->assertSame($message, $result);
        $this->assertSame('Hello, world!', $message->getContent());

        $track = [
            'platform' => 'spotify',
            'track_id' => 'abc123',
            'fallback_ids' => ['deezer' => 'xyz789'],
        ];
        $result = $message->setTrack($track);
        $this->assertSame($message, $result);
        $this->assertSame($track, $message->getTrack());

        $trackMetadata = [
            'title' => 'Song Title',
            'artist' => 'Artist Name',
            'album_cover' => 'https://example.com/cover.jpg',
            'preview_url' => 'https://example.com/preview.mp3',
            'platform_link' => 'https://spotify.com/track/abc123',
            'availability' => 'available',
        ];
        $result = $message->setTrackMetadata($trackMetadata);
        $this->assertSame($message, $result);
        $this->assertSame($trackMetadata, $message->getTrackMetadata());

        $result = $message->setIsRead(true);
        $this->assertSame($message, $result);
        $this->assertTrue($message->isRead());

        $readAt = new \DateTimeImmutable('2024-01-15 10:30:00');
        $result = $message->setReadAt($readAt);
        $this->assertSame($message, $result);
        $this->assertSame($readAt, $message->getReadAt());
    }

    public function testDefaultValues(): void
    {
        $message = new Message();

        $this->assertSame(Message::TYPE_TEXT, $message->getType());
        $this->assertNull($message->getContent());
        $this->assertNull($message->getTrack());
        $this->assertNull($message->getTrackMetadata());
        $this->assertFalse($message->isRead());
        $this->assertNull($message->getReadAt());
    }

    public function testIsMusicMessage(): void
    {
        $message = new Message();

        $message->setType(Message::TYPE_TEXT);
        $this->assertFalse($message->isMusicMessage());

        $message->setType(Message::TYPE_MUSIC);
        $this->assertTrue($message->isMusicMessage());
    }

    public function testMarkAsRead(): void
    {
        $message = new Message();

        $this->assertFalse($message->isRead());
        $this->assertNull($message->getReadAt());

        $result = $message->markAsRead();

        $this->assertSame($message, $result);
        $this->assertTrue($message->isRead());
        $this->assertInstanceOf(\DateTimeImmutable::class, $message->getReadAt());
        $this->assertEqualsWithDelta(
            new \DateTimeImmutable(),
            $message->getReadAt(),
            1
        );
    }

    public function testTypeConstants(): void
    {
        $this->assertSame('text', Message::TYPE_TEXT);
        $this->assertSame('music', Message::TYPE_MUSIC);
    }

    public function testTimeStampableTrait(): void
    {
        $message = new Message();
        $message->setCreatedAt();

        $this->assertInstanceOf(\DateTimeImmutable::class, $message->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $message->getUpdatedAt());
    }

    public function testGetConversationId(): void
    {
        $message = new Message();
        $conversation = new Conversation();

        // We need to use reflection to set a private id on the conversation
        $reflection = new \ReflectionClass($conversation);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($conversation, 123);

        $message->setConversation($conversation);

        $this->assertSame(123, $message->getConversationId());
    }
}
