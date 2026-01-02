<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use App\Entity\Message;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ConversationTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $conversation = new Conversation();

        $this->assertNull($conversation->getId());

        $result = $conversation->setIsGroup(true);
        $this->assertSame($conversation, $result);
        $this->assertTrue($conversation->isGroup());

        $result = $conversation->setGroupName('My Group Chat');
        $this->assertSame($conversation, $result);
        $this->assertSame('My Group Chat', $conversation->getGroupName());

        $result = $conversation->setUnreadCount(5);
        $this->assertSame($conversation, $result);
        $this->assertSame(5, $conversation->getUnreadCount());
    }

    public function testDefaultValues(): void
    {
        $conversation = new Conversation();

        $this->assertFalse($conversation->isGroup());
        $this->assertNull($conversation->getGroupName());
        $this->assertSame(0, $conversation->getUnreadCount());
    }

    public function testParticipantsCollection(): void
    {
        $conversation = new Conversation();
        $participant1 = new ConversationParticipant();
        $participant1->setUser(new User());
        $participant2 = new ConversationParticipant();
        $participant2->setUser(new User());

        $this->assertCount(0, $conversation->getParticipants());

        $result = $conversation->addParticipant($participant1);
        $this->assertSame($conversation, $result);
        $this->assertCount(1, $conversation->getParticipants());
        $this->assertTrue($conversation->getParticipants()->contains($participant1));

        $conversation->addParticipant($participant2);
        $this->assertCount(2, $conversation->getParticipants());

        // Test adding same participant twice (should not duplicate)
        $conversation->addParticipant($participant1);
        $this->assertCount(2, $conversation->getParticipants());

        $result = $conversation->removeParticipant($participant1);
        $this->assertSame($conversation, $result);
        $this->assertCount(1, $conversation->getParticipants());
        $this->assertFalse($conversation->getParticipants()->contains($participant1));
    }

    public function testMessagesCollection(): void
    {
        $conversation = new Conversation();
        $message1 = new Message();
        $message2 = new Message();

        $this->assertCount(0, $conversation->getMessages());

        $result = $conversation->addMessage($message1);
        $this->assertSame($conversation, $result);
        $this->assertCount(1, $conversation->getMessages());
        $this->assertTrue($conversation->getMessages()->contains($message1));

        $conversation->addMessage($message2);
        $this->assertCount(2, $conversation->getMessages());

        // Test adding same message twice (should not duplicate)
        $conversation->addMessage($message1);
        $this->assertCount(2, $conversation->getMessages());

        $result = $conversation->removeMessage($message1);
        $this->assertSame($conversation, $result);
        $this->assertCount(1, $conversation->getMessages());
        $this->assertFalse($conversation->getMessages()->contains($message1));
    }

    public function testGetMemberCount(): void
    {
        $conversation = new Conversation();

        $participant1 = new ConversationParticipant();
        $participant1->setUser(new User());
        $conversation->addParticipant($participant1);

        $participant2 = new ConversationParticipant();
        $participant2->setUser(new User());
        $conversation->addParticipant($participant2);

        $participant3 = new ConversationParticipant();
        $participant3->setUser(new User());
        $participant3->setLeftAt(new \DateTimeImmutable()); // Left the conversation
        $conversation->addParticipant($participant3);

        // Should only count active participants (not left)
        $this->assertSame(2, $conversation->getMemberCount());
    }

    public function testGetActiveParticipants(): void
    {
        $conversation = new Conversation();

        $participant1 = new ConversationParticipant();
        $participant1->setUser(new User());
        $conversation->addParticipant($participant1);

        $participant2 = new ConversationParticipant();
        $participant2->setUser(new User());
        $participant2->setLeftAt(new \DateTimeImmutable());
        $conversation->addParticipant($participant2);

        $activeParticipants = $conversation->getActiveParticipants();

        $this->assertCount(1, $activeParticipants);
        $this->assertTrue($activeParticipants->contains($participant1));
        $this->assertFalse($activeParticipants->contains($participant2));
    }

    public function testGetType(): void
    {
        $conversation = new Conversation();

        $this->assertSame('private', $conversation->getType());

        $conversation->setIsGroup(true);
        $this->assertSame('group', $conversation->getType());
    }

    public function testTimeStampableTrait(): void
    {
        $conversation = new Conversation();
        $conversation->setCreatedAt();

        $this->assertInstanceOf(\DateTimeImmutable::class, $conversation->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $conversation->getUpdatedAt());
    }

    public function testGetParticipantsList(): void
    {
        $conversation = new Conversation();
        $user1 = new User();

        // Use reflection to set user ID
        $reflection = new \ReflectionClass($user1);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($user1, 1);

        $user1->setUsername('user1');
        $user1->setProfilePicture('https://example.com/user1.jpg');

        $participant = new ConversationParticipant();
        $participant->setUser($user1);
        $participant->setRole(ConversationParticipant::ROLE_ADMIN);
        $participant->setJoinedAt(new \DateTimeImmutable('2024-01-01 10:00:00'));

        $conversation->addParticipant($participant);

        $participantsList = $conversation->getParticipantsList();

        $this->assertIsArray($participantsList);
        $this->assertCount(1, $participantsList);
        $this->assertSame(1, $participantsList[0]['user_id']);
        $this->assertSame('user1', $participantsList[0]['username']);
        $this->assertSame('https://example.com/user1.jpg', $participantsList[0]['profile_picture']);
        $this->assertSame(ConversationParticipant::ROLE_ADMIN, $participantsList[0]['role']);
        $this->assertNull($participantsList[0]['left_at']);
    }

    public function testGetParticipantsInfo(): void
    {
        $conversation = new Conversation();
        $user1 = new User();

        // Use reflection to set user ID
        $reflection = new \ReflectionClass($user1);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($user1, 42);

        $user1->setUsername('alice');
        $user1->setProfilePicture('https://example.com/alice.jpg');

        $participant = new ConversationParticipant();
        $participant->setUser($user1);
        $conversation->addParticipant($participant);

        $participantsInfo = $conversation->getParticipantsInfo();

        $this->assertIsArray($participantsInfo);
        $this->assertCount(1, $participantsInfo);
        $this->assertSame(42, $participantsInfo[0]['id']);
        $this->assertSame('alice', $participantsInfo[0]['username']);
        $this->assertSame('https://example.com/alice.jpg', $participantsInfo[0]['profile_picture']);
    }

    public function testGetLastMessageReturnsNullWhenNoMessages(): void
    {
        $conversation = new Conversation();

        $this->assertNull($conversation->getLastMessage());
    }

    public function testGetLastMessageReturnsLastMessageForTextMessage(): void
    {
        $conversation = new Conversation();
        $user = new User();

        // Use reflection to set IDs
        $userReflection = new \ReflectionClass($user);
        $userIdProperty = $userReflection->getProperty('id');
        $userIdProperty->setAccessible(true);
        $userIdProperty->setValue($user, 10);
        $user->setUsername('bob');

        $message = new Message();
        $messageReflection = new \ReflectionClass($message);
        $messageIdProperty = $messageReflection->getProperty('id');
        $messageIdProperty->setAccessible(true);
        $messageIdProperty->setValue($message, 5);

        $message->setAuthor($user);
        $message->setType(Message::TYPE_TEXT);
        $message->setContent('Hello world');
        $message->setCreatedAt();

        $conversation->addMessage($message);

        $lastMessage = $conversation->getLastMessage();

        $this->assertIsArray($lastMessage);
        $this->assertSame(5, $lastMessage['id']);
        $this->assertSame(Message::TYPE_TEXT, $lastMessage['type']);
        $this->assertSame('Hello world', $lastMessage['content']);
        $this->assertSame('Hello world', $lastMessage['preview']);
        $this->assertSame(10, $lastMessage['author']['id']);
        $this->assertSame('bob', $lastMessage['author']['username']);
    }

    public function testGetLastMessageReturnsPreviewForMusicMessage(): void
    {
        $conversation = new Conversation();
        $user = new User();

        // Use reflection to set IDs
        $userReflection = new \ReflectionClass($user);
        $userIdProperty = $userReflection->getProperty('id');
        $userIdProperty->setAccessible(true);
        $userIdProperty->setValue($user, 20);
        $user->setUsername('charlie');

        $message = new Message();
        $messageReflection = new \ReflectionClass($message);
        $messageIdProperty = $messageReflection->getProperty('id');
        $messageIdProperty->setAccessible(true);
        $messageIdProperty->setValue($message, 15);

        $message->setAuthor($user);
        $message->setType(Message::TYPE_MUSIC);
        $message->setCreatedAt();

        $conversation->addMessage($message);

        $lastMessage = $conversation->getLastMessage();

        $this->assertIsArray($lastMessage);
        $this->assertSame('Vous a partagé une musique', $lastMessage['preview']);
    }
}
