<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use App\Entity\Message;
use App\Entity\User;
use App\Util\ReflectionUtil;
use PHPUnit\Framework\TestCase;

class ConversationTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $conversation = new Conversation();

        $this->assertNull($conversation->getId());

        $result = $conversation->setIsGroup(true);
        $this->assertSame($conversation, $result);
        $this->assertTrue($conversation->getIsGroup());

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

        $this->assertFalse($conversation->getIsGroup());
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

        $this->assertFalse($conversation->getIsGroup());

        $conversation->setIsGroup(true);
        $this->assertTrue($conversation->getIsGroup());
    }

    public function testTimeStampableTrait(): void
    {
        $conversation = new Conversation();
        $conversation->setCreatedAt();

        $this->assertSame(
            $conversation->getCreatedAt(),
            $conversation->getUpdatedAt(),
        );
    }

    public function testGetParticipants(): void
    {
        $conversation = new Conversation();

        $user1 = new User();
        ReflectionUtil::setPropertyValue($user1, 'id', 1);
        $user1->setUsername('user1');
        $user1->setProfilePicture('https://example.com/user1.jpg');

        $participant = new ConversationParticipant();
        $participant->setUser($user1);
        $participant->setRole(ConversationParticipant::ROLE_ADMIN);
        $participant->setJoinedAt(new \DateTimeImmutable('2024-01-01 10:00:00'));

        $conversation->addParticipant($participant);

        $participants = $conversation->getParticipants();

        $this->assertSame(1, $participants->first()->getUser()->getId());
        $this->assertSame('user1', $participants->first()->getUser()->getUsername());
        $this->assertSame('https://example.com/user1.jpg', $participants->first()->getUser()->getProfilePicture());
        $this->assertTrue($conversation->isAdmin($participants->first()->getUser()));
        $this->assertNull($participants->first()->getLeftAt());
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
        ReflectionUtil::setPropertyValue($user, 'id', 10);
        $user->setUsername('bob');

        $message = new Message();
        ReflectionUtil::setPropertyValue($message, 'id', 5);
        $message->setAuthor($user);
        $message->setType(Message::TYPE_TEXT);
        $message->setContent('Hello world');
        $message->setCreatedAt();

        $conversation->addMessage($message);

        $lastMessage = $conversation->getLastMessage();

        $this->assertSame(5, $lastMessage->getId());
        $this->assertSame(Message::TYPE_TEXT, $lastMessage->getType());
        $this->assertSame('Hello world', $lastMessage->getContent());
        $this->assertSame('Hello world', $lastMessage->getMessagePreview());
        $this->assertSame(10, $lastMessage->getAuthor()->getId());
        $this->assertSame('bob', $lastMessage->getAuthor()->getUsername());
    }

    public function testGetLastMessageReturnsPreviewForMusicMessage(): void
    {
        $conversation = new Conversation();

        $user = new User();
        ReflectionUtil::setPropertyValue($user, 'id', 20);
        $user->setUsername('charlie');

        $message = new Message();
        ReflectionUtil::setPropertyValue($message, 'id', 15);
        $message->setAuthor($user);
        $message->setType(Message::TYPE_MUSIC);
        $message->setCreatedAt();

        $conversation->addMessage($message);

        $lastMessage = $conversation->getLastMessage();

        $this->assertSame('Vous a partagé une musique', $lastMessage->getMessagePreview());
    }

    public function testGetParticipantForUser(): void
    {
        $conversation = new Conversation();
        $user1 = new User();
        $participant1 = new ConversationParticipant();
        $participant1->setUser($user1);
        $conversation->addParticipant($participant1);

        $user2 = new User();

        $this->assertSame($participant1, $conversation->getParticipantForUser($user1));
        $this->assertNull($conversation->getParticipantForUser($user2));
    }

    public function testGetFlattenedList(): void
    {
        $conversation = new Conversation();
        $user1 = new User();
        ReflectionUtil::setPropertyValue($user1, 'id', 1);
        $participant = new ConversationParticipant();
        $participant->setUser($user1);
        $conversation->addParticipant($participant);

        $this->assertContains($user1, $conversation->getFlattenedParticipants());
    }

    public function testHasUser(): void
    {
        $conversation = new Conversation();
        $user1 = new User();
        ReflectionUtil::setPropertyValue($user1, 'id', 1);

        $participant = new ConversationParticipant();
        $participant->setUser($user1);
        $conversation->addParticipant($participant);

        $this->assertTrue($conversation->hasUser($user1));

        $emptyConversation = new Conversation();
        $this->assertFalse($emptyConversation->hasUser($user1));
    }
}
