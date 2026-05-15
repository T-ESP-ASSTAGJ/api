<?php

declare(strict_types=1);

namespace App\Tests\State\Conversation;

use ApiPlatform\Metadata\GetCollection;
use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use App\Entity\User;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\State\Conversation\ConversationListProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ConversationListProviderTest extends TestCase
{
    /**
     * @var ConversationRepository&\PHPUnit\Framework\MockObject\MockObject
     */
    private ConversationRepository $conversationRepo;

    /**
     * @var MessageRepository&\PHPUnit\Framework\MockObject\MockObject
     */
    private MessageRepository $messageRepo;

    /**
     * @var Security&\PHPUnit\Framework\MockObject\MockObject
     */
    private Security $security;

    private ConversationListProvider $provider;

    protected function setUp(): void
    {
        $this->conversationRepo = $this->createMock(ConversationRepository::class);
        $this->messageRepo = $this->createMock(MessageRepository::class);
        $this->security = $this->createMock(Security::class);
        $this->provider = new ConversationListProvider(
            $this->conversationRepo,
            $this->messageRepo,
            $this->security,
        );
    }

    public function testThrowsWhenUnauthenticated(): void
    {
        $this->security->method('getUser')->willReturn(null);

        $this->expectException(UnauthorizedHttpException::class);
        $this->provider->provide(new GetCollection());
    }

    public function testReturnsConversationsWithUnreadCounts(): void
    {
        $user = new User();
        $this->security->method('getUser')->willReturn($user);

        $participant = $this->createMock(ConversationParticipant::class);
        $participant->method('getLastReadAt')->willReturn(null);

        $conversation = $this->createMock(Conversation::class);
        $conversation->method('getParticipantForUser')->willReturn($participant);
        $conversation->expects($this->once())->method('setUnreadCount')->with(3);

        $this->conversationRepo->method('findByUser')->willReturn([$conversation]);
        $this->messageRepo->method('countUnreadMessages')->willReturn(3);

        $result = $this->provider->provide(new GetCollection());

        $this->assertCount(1, $result);
        $this->assertSame($conversation, $result[0]);
    }

    public function testHandlesNoParticipantEntry(): void
    {
        $user = new User();
        $this->security->method('getUser')->willReturn($user);

        $conversation = $this->createMock(Conversation::class);
        $conversation->method('getParticipantForUser')->willReturn(null);
        $conversation->expects($this->once())->method('setUnreadCount')->with(0);

        $this->conversationRepo->method('findByUser')->willReturn([$conversation]);
        $this->messageRepo->method('countUnreadMessages')->willReturn(0);

        $this->provider->provide(new GetCollection());
    }
}
