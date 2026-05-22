<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\Entity\User;
use App\Message\FollowCreatedMessage;
use App\MessageHandler\FollowCreatedHandler;
use App\Repository\UserRepository;
use App\Service\PushNotificationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FollowCreatedHandlerTest extends TestCase
{
    /**
     * @var PushNotificationService&MockObject
     */
    private PushNotificationService $pushNotificationService;

    /**
     * @var UserRepository&MockObject
     */
    private UserRepository $userRepository;

    private FollowCreatedHandler $handler;

    protected function setUp(): void
    {
        $this->pushNotificationService = $this->createMock(PushNotificationService::class);
        $this->userRepository = $this->createMock(UserRepository::class);

        $this->handler = new FollowCreatedHandler(
            $this->pushNotificationService,
            $this->userRepository,
        );
    }

    public function testInvokeSendsPushNotification(): void
    {
        $followerId = 1;
        $followerUsername = 'JohnDoe';
        $followerPic = 'https://example.com/photo.jpg';
        $followedId = 42;

        $followedParameters = $this->createMock(\App\Entity\UserParameter::class);
        $followedParameters->method('getNotifNewFollower')->willReturn(true);

        $userToFollow = $this->createMock(User::class);
        $userToFollow->method('getId')->willReturn($followedId);
        $userToFollow->method('getParameters')->willReturn($followedParameters);

        $currentUser = $this->createMock(User::class);
        $currentUser->method('getId')->willReturn($followerId);
        $currentUser->method('getUsername')->willReturn($followerUsername);
        $currentUser->method('getProfilePicture')->willReturn($followerPic);

        $this->userRepository->method('find')->willReturnMap([
            [$followedId, null, null, $userToFollow],
            [$followerId, null, null, $currentUser],
        ]);

        $this->pushNotificationService
            ->expects($this->once())
            ->method('sendToUser')
            ->with(
                $followedId,
                $followerUsername,
                'just followed you',
                [
                    'userId' => $followerId,
                    'profilePicture' => $followerPic,
                ],
            )
        ;

        ($this->handler)(new FollowCreatedMessage($followerId, $followedId));
    }

    public function testInvokeDoesNotSendPushNotificationIfDisabled(): void
    {
        $followedParameters = $this->createMock(\App\Entity\UserParameter::class);
        $followedParameters->method('getNotifNewFollower')->willReturn(false);

        $userToFollow = $this->createMock(User::class);
        $userToFollow->method('getParameters')->willReturn($followedParameters);

        $currentUser = $this->createMock(User::class);

        $this->userRepository->method('find')->willReturnMap([
            [42, null, null, $userToFollow],
            [1, null, null, $currentUser],
        ]);

        $this->pushNotificationService->expects($this->never())->method('sendToUser');

        ($this->handler)(new FollowCreatedMessage(1, 42));
    }

    public function testInvokeDoesNothingIfUserNotFound(): void
    {
        $this->userRepository->method('find')->willReturn(null);

        $this->pushNotificationService->expects($this->never())->method('sendToUser');

        ($this->handler)(new FollowCreatedMessage(1, 42));
    }
}
