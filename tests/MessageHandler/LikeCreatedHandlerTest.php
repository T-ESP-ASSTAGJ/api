<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\Entity\Like;
use App\Entity\User;
use App\Message\LikeCreatedMessage;
use App\MessageHandler\LikeCreatedHandler;
use App\Repository\LikeRepository;
use App\Service\PushNotificationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LikeCreatedHandlerTest extends TestCase
{
    private LikeRepository&MockObject $likeRepository;
    private PushNotificationService&MockObject $pushNotificationService;
    private LikeCreatedHandler $handler;

    protected function setUp(): void
    {
        $this->likeRepository = $this->createMock(LikeRepository::class);
        $this->pushNotificationService = $this->createMock(PushNotificationService::class);

        $this->handler = new LikeCreatedHandler(
            $this->likeRepository,
            $this->pushNotificationService
        );
    }

    public function testInvokeReturnsEarlyIfLikeNotFound(): void
    {
        $this->likeRepository->expects($this->once())
            ->method('find')
            ->with(123)
            ->willReturn(null);

        $this->pushNotificationService->expects($this->never())
            ->method('sendToUser');

        ($this->handler)(new LikeCreatedMessage(123));
    }

    public function testInvokeReturnsEarlyIfRecipientIsLiker(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $like = $this->createMock(Like::class);
        $like->method('getUser')->willReturn($user);

        $this->likeRepository->method('find')->willReturn($like);

        $this->likeRepository->method('findContentOwner')->willReturn($user);

        $this->pushNotificationService->expects($this->never())
            ->method('sendToUser');

        ($this->handler)(new LikeCreatedMessage(123));
    }

    public function testInvokeSendsPushNotificationSuccessfully(): void
    {
        $liker = $this->createMock(User::class);
        $liker->method('getId')->willReturn(1);
        $liker->method('getUsername')->willReturn('JaneDoe');

        $recipient = $this->createMock(User::class);
        $recipient->method('getId')->willReturn(99);

        $like = $this->createMock(Like::class);
        $like->method('getUser')->willReturn($liker);
        $like->method('getEntityClass')->willReturn('Post');
        $like->method('getEntityId')->willReturn(456);

        $this->likeRepository->method('find')->with(123)->willReturn($like);
        $this->likeRepository->method('findContentOwner')->with($like)->willReturn($recipient);

        $this->pushNotificationService->expects($this->once())
            ->method('sendToUser')
            ->with(
                99,
                'New like',
                'JaneDoe liked your Post',
                [
                    'type' => 'like',
                    'entity_class' => 'Post',
                    'entity_id' => '456',
                ]
            );

        ($this->handler)(new LikeCreatedMessage(123));
    }
}