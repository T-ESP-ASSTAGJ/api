<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\Entity\User;
use App\Message\FollowCreatedMessage;
use App\MessageHandler\FollowCreatedHandler;
use App\Service\PushNotificationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FollowCreatedHandlerTest extends TestCase
{
    /** @var PushNotificationService&MockObject */
    private PushNotificationService $pushNotificationService;

    private FollowCreatedHandler $handler;

    protected function setUp(): void
    {
        $this->pushNotificationService = $this->createMock(PushNotificationService::class);

        $this->handler = new FollowCreatedHandler(
            $this->pushNotificationService
        );
    }

    public function testInvokeSendsPushNotification(): void
    {
        $followerId = 1;
        $followerUsername = 'JohnDoe';
        $followerPic = 'https://example.com/photo.jpg';

        $followedId = 42;

        $userToFollow = $this->createMock(User::class);
        $userToFollow->method('getId')->willReturn($followedId);

        $currentUser = $this->createMock(User::class);
        $currentUser->method('getId')->willReturn($followerId);
        $currentUser->method('getUsername')->willReturn($followerUsername);
        $currentUser->method('getProfilePicture')->willReturn($followerPic);

        $message = new FollowCreatedMessage($currentUser, $userToFollow);

        $this->pushNotificationService
            ->expects($this->once())
            ->method('sendToUser')
            ->with(
                $followedId,
                $followerUsername,
                'just followed you! 🤤',
                [
                    'userId' => $followerId,
                    'profilePicture' => $followerPic,
                ]
            );

        ($this->handler)($message);
    }
}
