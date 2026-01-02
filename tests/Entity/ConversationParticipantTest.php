<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ConversationParticipantTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $participant = new ConversationParticipant();
        $conversation = new Conversation();
        $user = new User();

        $this->assertNull($participant->getId());

        $result = $participant->setConversation($conversation);
        $this->assertSame($participant, $result);
        $this->assertSame($conversation, $participant->getConversation());

        $result = $participant->setUser($user);
        $this->assertSame($participant, $result);
        $this->assertSame($user, $participant->getUser());

        $joinedAt = new \DateTimeImmutable('2024-01-15 10:00:00');
        $result = $participant->setJoinedAt($joinedAt);
        $this->assertSame($participant, $result);
        $this->assertSame($joinedAt, $participant->getJoinedAt());

        $leftAt = new \DateTimeImmutable('2024-01-20 15:00:00');
        $result = $participant->setLeftAt($leftAt);
        $this->assertSame($participant, $result);
        $this->assertSame($leftAt, $participant->getLeftAt());

        $result = $participant->setRole(ConversationParticipant::ROLE_ADMIN);
        $this->assertSame($participant, $result);
        $this->assertSame(ConversationParticipant::ROLE_ADMIN, $participant->getRole());

        $result = $participant->setUnreadCount(5);
        $this->assertSame($participant, $result);
        $this->assertSame(5, $participant->getUnreadCount());
    }

    public function testDefaultValues(): void
    {
        $participant = new ConversationParticipant();

        $this->assertSame(ConversationParticipant::ROLE_MEMBER, $participant->getRole());
        $this->assertNull($participant->getLeftAt());
        $this->assertSame(0, $participant->getUnreadCount());
        $this->assertInstanceOf(\DateTimeImmutable::class, $participant->getJoinedAt());
    }

    public function testIsActive(): void
    {
        $participant = new ConversationParticipant();

        $this->assertTrue($participant->isActive());

        $participant->setLeftAt(new \DateTimeImmutable());
        $this->assertFalse($participant->isActive());

        $participant->setLeftAt(null);
        $this->assertTrue($participant->isActive());
    }

    public function testLeave(): void
    {
        $participant = new ConversationParticipant();

        $this->assertNull($participant->getLeftAt());
        $this->assertTrue($participant->isActive());

        $result = $participant->leave();

        $this->assertSame($participant, $result);
        $this->assertInstanceOf(\DateTimeImmutable::class, $participant->getLeftAt());
        $this->assertFalse($participant->isActive());
        $this->assertEqualsWithDelta(
            new \DateTimeImmutable(),
            $participant->getLeftAt(),
            1
        );
    }

    public function testIsAdmin(): void
    {
        $participant = new ConversationParticipant();

        $this->assertFalse($participant->isAdmin());

        $participant->setRole(ConversationParticipant::ROLE_ADMIN);
        $this->assertTrue($participant->isAdmin());

        $participant->setRole(ConversationParticipant::ROLE_MEMBER);
        $this->assertFalse($participant->isAdmin());
    }

    public function testPromoteToAdmin(): void
    {
        $participant = new ConversationParticipant();

        $this->assertSame(ConversationParticipant::ROLE_MEMBER, $participant->getRole());

        $result = $participant->promoteToAdmin();

        $this->assertSame($participant, $result);
        $this->assertSame(ConversationParticipant::ROLE_ADMIN, $participant->getRole());
        $this->assertTrue($participant->isAdmin());
    }

    public function testDemoteToMember(): void
    {
        $participant = new ConversationParticipant();
        $participant->setRole(ConversationParticipant::ROLE_ADMIN);

        $this->assertTrue($participant->isAdmin());

        $result = $participant->demoteToMember();

        $this->assertSame($participant, $result);
        $this->assertSame(ConversationParticipant::ROLE_MEMBER, $participant->getRole());
        $this->assertFalse($participant->isAdmin());
    }

    public function testIncrementUnreadCount(): void
    {
        $participant = new ConversationParticipant();

        $this->assertSame(0, $participant->getUnreadCount());

        $result = $participant->incrementUnreadCount();
        $this->assertSame($participant, $result);
        $this->assertSame(1, $participant->getUnreadCount());

        $participant->incrementUnreadCount();
        $this->assertSame(2, $participant->getUnreadCount());
    }

    public function testResetUnreadCount(): void
    {
        $participant = new ConversationParticipant();
        $participant->setUnreadCount(10);

        $this->assertSame(10, $participant->getUnreadCount());

        $result = $participant->resetUnreadCount();

        $this->assertSame($participant, $result);
        $this->assertSame(0, $participant->getUnreadCount());
    }

    public function testRoleConstants(): void
    {
        $this->assertSame('admin', ConversationParticipant::ROLE_ADMIN);
        $this->assertSame('member', ConversationParticipant::ROLE_MEMBER);
    }
}
