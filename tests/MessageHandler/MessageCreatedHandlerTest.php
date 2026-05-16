<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use App\Entity\Enum\VisibilityEnum;
use App\Entity\Message;
use App\Entity\User;
use App\Message\MessageCreatedMessage;
use App\MessageHandler\MessageCreatedHandler;
use App\Repository\ConversationRepository;
use App\Repository\FollowRepository;
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

    /**
     * @var FollowRepository&MockObject
     */
    private FollowRepository $followRepository;

    private MessageCreatedHandler $handler;

    protected function setUp(): void
    {
        $this->pushNotificationService = $this->createMock(PushNotificationService::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->conversationRepository = $this->createMock(ConversationRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->followRepository = $this->createMock(FollowRepository::class);

        $this->handler = new MessageCreatedHandler(
            $this->pushNotificationService,
            $this->userRepository,
            $this->conversationRepository,
            $this->entityManager,
            $this->followRepository,
        );
    }

    public function testInvokeSendsNotificationsToOtherParticipants(): void
    {
        $senderId = 1;
        $recipientId = 2;
        $conversationId = 10;

        $sender = $this->createMock(User::class);
        $sender->method('getId')->willReturn($senderId);
        $sender->method('getUsername')->willReturn('Sender');
        $sender->method('getProfilePicture')->willReturn('sender.jpg');

        $recipientParameters = $this->createMock(\App\Entity\UserParameter::class);
        $recipientParameters->method('getNotifNewMessage')->willReturn(VisibilityEnum::Public);

        $recipient = $this->createMock(User::class);
        $recipient->method('getId')->willReturn($recipientId);
        $recipient->method('getParameters')->willReturn($recipientParameters);

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

        $this->userRepository->method('find')->with($senderId)->willReturn($sender);
        $this->conversationRepository->method('find')->with($conversationId)->willReturn($conversation);
        $this->mockMessageRepository();

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

        ($this->handler)(new MessageCreatedMessage($conversationId, $senderId, 100));
    }

    public function testInvokeSendsGroupNotificationsToOtherParticipants(): void
    {
        $senderId = 1;
        $recipientId = 2;
        $conversationId = 10;

        $sender = $this->createMock(User::class);
        $sender->method('getId')->willReturn($senderId);
        $sender->method('getUsername')->willReturn('Sender');
        $sender->method('getProfilePicture')->willReturn('sender.jpg');

        $recipientParameters = $this->createMock(\App\Entity\UserParameter::class);
        $recipientParameters->method('getNotifNewMessage')->willReturn(VisibilityEnum::Public);

        $recipient = $this->createMock(User::class);
        $recipient->method('getId')->willReturn($recipientId);
        $recipient->method('getParameters')->willReturn($recipientParameters);

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

        $this->userRepository->method('find')->with($senderId)->willReturn($sender);
        $this->conversationRepository->method('find')->with($conversationId)->willReturn($conversation);
        $this->mockMessageRepository();

        $this->pushNotificationService->expects($this->once())
            ->method('sendToUser')
            ->with(
                $recipientId,
                'Cool Group',
                'Sender just sent a message.',
                [
                    'conversationId' => $conversationId,
                    'profilePicture' => 'sender.jpg',
                ],
            )
        ;

        ($this->handler)(new MessageCreatedMessage($conversationId, $senderId, 100));
    }

    public function testInvokeDoesNotSendNotificationsIfPrivate(): void
    {
        $senderId = 1;
        $recipientId = 2;

        $sender = $this->createMock(User::class);
        $sender->method('getId')->willReturn($senderId);

        $recipientParameters = $this->createMock(\App\Entity\UserParameter::class);
        $recipientParameters->method('getNotifNewMessage')->willReturn(VisibilityEnum::Private);

        $recipient = $this->createMock(User::class);
        $recipient->method('getId')->willReturn($recipientId);
        $recipient->method('getParameters')->willReturn($recipientParameters);

        $senderParticipant = $this->createMock(ConversationParticipant::class);
        $senderParticipant->method('getUser')->willReturn($sender);

        $recipientParticipant = $this->createMock(ConversationParticipant::class);
        $recipientParticipant->method('getUser')->willReturn($recipient);

        $conversation = $this->createMock(Conversation::class);
        $conversation->method('getActiveParticipants')->willReturn(new ArrayCollection([
            $senderParticipant,
            $recipientParticipant,
        ]));

        $this->userRepository->method('find')->with($senderId)->willReturn($sender);
        $this->conversationRepository->method('find')->willReturn($conversation);
        $this->mockMessageRepository();

        $this->pushNotificationService->expects($this->never())->method('sendToUser');

        ($this->handler)(new MessageCreatedMessage(10, $senderId, 100));
    }

    public function testInvokeSendsNotificationIfFriendsAndMutualFollow(): void
    {
        $senderId = 1;
        $recipientId = 2;
        $conversationId = 10;

        $sender = $this->createMock(User::class);
        $sender->method('getId')->willReturn($senderId);
        $sender->method('getUsername')->willReturn('Sender');
        $sender->method('getProfilePicture')->willReturn('sender.jpg');

        $recipientParameters = $this->createMock(\App\Entity\UserParameter::class);
        $recipientParameters->method('getNotifNewMessage')->willReturn(VisibilityEnum::Friends);

        $recipient = $this->createMock(User::class);
        $recipient->method('getId')->willReturn($recipientId);
        $recipient->method('getParameters')->willReturn($recipientParameters);

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

        $this->userRepository->method('find')->with($senderId)->willReturn($sender);
        $this->conversationRepository->method('find')->willReturn($conversation);
        $this->followRepository->method('isMutualFollow')->with($senderId, $recipientId)->willReturn(true);
        $this->mockMessageRepository();

        $this->pushNotificationService->expects($this->once())->method('sendToUser');

        ($this->handler)(new MessageCreatedMessage($conversationId, $senderId, 100));
    }

    public function testInvokeDoesNotSendNotificationIfFriendsAndNotMutualFollow(): void
    {
        $senderId = 1;
        $recipientId = 2;

        $sender = $this->createMock(User::class);
        $sender->method('getId')->willReturn($senderId);

        $recipientParameters = $this->createMock(\App\Entity\UserParameter::class);
        $recipientParameters->method('getNotifNewMessage')->willReturn(VisibilityEnum::Friends);

        $recipient = $this->createMock(User::class);
        $recipient->method('getId')->willReturn($recipientId);
        $recipient->method('getParameters')->willReturn($recipientParameters);

        $senderParticipant = $this->createMock(ConversationParticipant::class);
        $senderParticipant->method('getUser')->willReturn($sender);

        $recipientParticipant = $this->createMock(ConversationParticipant::class);
        $recipientParticipant->method('getUser')->willReturn($recipient);

        $conversation = $this->createMock(Conversation::class);
        $conversation->method('getActiveParticipants')->willReturn(new ArrayCollection([
            $senderParticipant,
            $recipientParticipant,
        ]));

        $this->userRepository->method('find')->with($senderId)->willReturn($sender);
        $this->conversationRepository->method('find')->willReturn($conversation);
        $this->followRepository->method('isMutualFollow')->with($senderId, $recipientId)->willReturn(false);
        $this->mockMessageRepository();

        $this->pushNotificationService->expects($this->never())->method('sendToUser');

        ($this->handler)(new MessageCreatedMessage(10, $senderId, 100));
    }

    public function testInvokeDoesNothingIfEntityNotFound(): void
    {
        $this->userRepository->method('find')->willReturn(null);
        $this->conversationRepository->method('find')->willReturn(null);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn(null);
        $this->entityManager->method('getRepository')->willReturn($repo);

        $this->pushNotificationService->expects($this->never())->method('sendToUser');

        ($this->handler)(new MessageCreatedMessage(10, 1, 100));
    }

    private function mockMessageRepository(?Message $messageEntity = null): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn($messageEntity ?? $this->createMock(Message::class));
        $this->entityManager->method('getRepository')->with(Message::class)->willReturn($repo);
    }
}
