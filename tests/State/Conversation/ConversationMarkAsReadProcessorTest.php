<?php

declare(strict_types=1);

namespace App\Tests\State\Conversation;

use ApiPlatform\Metadata\Post;
use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use App\Entity\User;
use App\State\Conversation\ConversationMarkAsReadProcessor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ConversationMarkAsReadProcessorTest extends TestCase
{
    /**
     * @var EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private EntityManagerInterface $em;

    /**
     * @var Security&\PHPUnit\Framework\MockObject\MockObject
     */
    private Security $security;

    private ConversationMarkAsReadProcessor $processor;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->processor = new ConversationMarkAsReadProcessor($this->em, $this->security);
    }

    public function testThrowsWhenUnauthenticated(): void
    {
        $this->security->method('getUser')->willReturn(null);

        $this->expectException(UnauthorizedHttpException::class);
        $this->processor->process(new Conversation(), new Post());
    }

    public function testThrowsWhenNotParticipant(): void
    {
        $user = new User();
        $this->security->method('getUser')->willReturn($user);

        $conversation = $this->createMock(Conversation::class);
        $conversation->method('getParticipantForUser')->willReturn(null);

        $this->expectException(AccessDeniedHttpException::class);
        $this->processor->process($conversation, new Post());
    }

    public function testMarksConversationAsRead(): void
    {
        $user = new User();
        $this->security->method('getUser')->willReturn($user);

        $participant = $this->createMock(ConversationParticipant::class);
        $participant->expects($this->once())->method('setLastReadAt');

        $conversation = $this->createMock(Conversation::class);
        $conversation->method('getParticipantForUser')->willReturn($participant);

        $this->em->expects($this->once())->method('flush');

        $this->processor->process($conversation, new Post());
    }
}
