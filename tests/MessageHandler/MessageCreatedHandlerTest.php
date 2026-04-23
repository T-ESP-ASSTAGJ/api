<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use App\Entity\Message;
use App\Entity\User;
use App\Message\MessageCreatedMessage;
use App\MessageHandler\MessageCreatedHandler;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use App\Service\PushNotificationService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MessageCreatedHandlerTest extends TestCase
{
    /**
     * @var PushNotificationService&MockObject
     */
    private PushNotificationService $pushNotificationService;

    /**
     * @var UserRepository&MockObject
     */
    private UserRepository $userRepository;

    /**
     * @var ConversationRepository&MockObject
     */
    private ConversationRepository $conversationRepository;

    /**
     * @var EntityManagerInterface&MockObject
     */
    private EntityManagerInterface $entityManager;

    private MessageCreatedHandler $handler;

    protected function setUp(): void
    {
        $this->pushNotificationService = $this->createMock(PushNotificationService::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->conversationRepository = $this->createMock(ConversationRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->handler = new MessageCreatedHandler(
            $this->pushNotificationService,
            $this->userRepository,
            $this->conversationRepository,
            $this->entityManager,
        );
    }

    public function testInvokeSendsNotificationsToOtherParticipants(): void
    {
        $senderId = 1;
        $recipientId = 2;
        $conversationId = 10;
        $messageId = 100;

        $sender = $this->createMock(User::class);
        $sender->method('getId')->willReturn($senderId);
        $sender->method('getUsername')->willReturn('Sender');
        $sender->method('getProfilePicture')->willReturn('sender.jpg');

        $recipient = $this->createMock(User::class);
        $recipient->method('getId')->willReturn($recipientId);

        $senderParticipant = $this->createMock(ConversationParticipant::class);
        $senderParticipant->method('getUser')->willReturn($sender);

        $recipientParticipant = $this->createMock(ConversationParticipant::class);
        $recipientParticipant->method('getUser')->willReturn($recipient);

        $conversation = $this->createMock(Conversation::class);
        $conversation->method('getId')->willReturn($conversationId);
        $conversation->method('getActiveParticipants')->willReturn(new ArrayCollection([
            $senderParticipant,
            $recipientParticipant,
        ]));

        $messageEntity = $this->createMock(Message::class);
        $messageEntity->method('getMessagePreview')->willReturn('just sent a message');

        $this->userRepository->method('find')->with($senderId)->willReturn($sender);
        $this->conversationRepository->method('find')->with($conversationId)->willReturn($conversation);

        $messageRepository = $this->createMock(EntityRepository::class);
        $messageRepository->method('find')->with($messageId)->willReturn($messageEntity);
        $this->entityManager->method('getRepository')->with(Message::class)->willReturn($messageRepository);

        $message = new MessageCreatedMessage($conversationId, $senderId, $messageId);

        $this->pushNotificationService->expects($this->once())
            ->method('sendToUser')
            ->with(
                $recipientId,
                'Sender',
                'just sent a message',
                [
                    'conversationId' => $conversationId,
                    'profilePicture' => 'sender.jpg',
                ],
            )
        ;

        ($this->handler)($message);
    }

    public function testInvokeSendsGroupNotificationsToOtherParticipants(): void
    {
        $senderId = 1;
        $recipientId = 2;
        $conversationId = 10;
        $messageId = 100;

        $sender = $this->createMock(User::class);
        $sender->method('getId')->willReturn($senderId);
        $sender->method('getUsername')->willReturn('Sender');
        $sender->method('getProfilePicture')->willReturn('sender.jpg');

        $recipient = $this->createMock(User::class);
        $recipient->method('getId')->willReturn($recipientId);

        $senderParticipant = $this->createMock(ConversationParticipant::class);
        $senderParticipant->method('getUser')->willReturn($sender);

        $recipientParticipant = $this->createMock(ConversationParticipant::class);
        $recipientParticipant->method('getUser')->willReturn($recipient);

        $conversation = $this->createMock(Conversation::class);
        $conversation->method('getId')->willReturn($conversationId);
        $conversation->method('getIsGroup')->willReturn(true);
        $conversation->method('getGroupName')->willReturn('Cool Group');
        $conversation->method('getActiveParticipants')->willReturn(new ArrayCollection([
            $senderParticipant,
            $recipientParticipant,
        ]));

        $messageEntity = $this->createMock(Message::class);
        $messageEntity->method('getMessagePreview')->willReturn('Hello group!');

        $this->userRepository->method('find')->with($senderId)->willReturn($sender);
        $this->conversationRepository->method('find')->with($conversationId)->willReturn($conversation);

        $messageRepository = $this->createMock(EntityRepository::class);
        $messageRepository->method('find')->with($messageId)->willReturn($messageEntity);
        $this->entityManager->method('getRepository')->with(Message::class)->willReturn($messageRepository);

        $message = new MessageCreatedMessage($conversationId, $senderId, $messageId);

        $this->pushNotificationService->expects($this->once())
            ->method('sendToUser')
            ->with(
                $recipientId,
                'Cool Group',
                'Hello group!',
                [
                    'conversationId' => $conversationId,
                    'profilePicture' => 'sender.jpg',
                ],
            )
        ;

        ($this->handler)($message);
    }

    public function testInvokeDoesNothingIfEntityNotFound(): void
    {
        $this->userRepository->method('find')->willReturn(null);
        $this->conversationRepository->method('find')->willReturn(null);

        $messageRepository = $this->createMock(EntityRepository::class);
        $messageRepository->method('find')->willReturn(null);
        $this->entityManager->method('getRepository')->willReturn($messageRepository);

        $message = new MessageCreatedMessage(10, 1, 100);

        $this->pushNotificationService->expects($this->never())
            ->method('sendToUser')
        ;

        ($this->handler)($message);
    }
}
