<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\Entity\Post;
use App\Entity\User;
use App\Message\CommentCreatedMessage;
use App\MessageHandler\CommentCreatedHandler;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\PushNotificationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CommentCreatedHandlerTest extends TestCase
{
    /**
     * @var PushNotificationService&MockObject
     */
    private PushNotificationService $pushNotificationService;

    /**
     * @var PostRepository&MockObject
     */
    private PostRepository $postRepository;

    /**
     * @var UserRepository&MockObject
     */
    private UserRepository $userRepository;

    private CommentCreatedHandler $handler;

    protected function setUp(): void
    {
        $this->pushNotificationService = $this->createMock(PushNotificationService::class);
        $this->postRepository = $this->createMock(PostRepository::class);
        $this->userRepository = $this->createMock(UserRepository::class);

        $this->handler = new CommentCreatedHandler(
            $this->pushNotificationService,
            $this->postRepository,
            $this->userRepository,
        );
    }

    public function testInvokeSendsPushNotificationToPostOwner(): void
    {
        $commenterId = 10;
        $commenterUsername = 'JaneDoe';
        $commenterPic = 'https://example.com/jane.jpg';

        $postOwnerId = 99;
        $postId = 1;

        $commenter = $this->createMock(User::class);
        $commenter->method('getId')->willReturn($commenterId);
        $commenter->method('getUsername')->willReturn($commenterUsername);
        $commenter->method('getProfilePicture')->willReturn($commenterPic);

        $postOwnerParameters = $this->createMock(\App\Entity\UserParameter::class);
        $postOwnerParameters->method('getNotifNewComment')->willReturn(true);

        $postOwner = $this->createMock(User::class);
        $postOwner->method('getId')->willReturn($postOwnerId);
        $postOwner->method('getParameters')->willReturn($postOwnerParameters);

        $post = $this->createMock(Post::class);
        $post->method('getUser')->willReturn($postOwner);

        $this->postRepository->method('find')->with($postId)->willReturn($post);
        $this->userRepository->method('find')->with($commenterId)->willReturn($commenter);

        $message = new CommentCreatedMessage($postId, $commenterId);

        $this->pushNotificationService
            ->expects($this->once())
            ->method('sendToUser')
            ->with(
                $postOwnerId,
                $commenterUsername,
                'just commented your post',
                [
                    'userId' => $commenterId,
                    'profilePicture' => $commenterPic,
                ],
            )
        ;

        ($this->handler)($message);
    }

    public function testInvokeDoesNotSendPushNotificationIfDisabledByOwner(): void
    {
        $commenterId = 10;
        $postOwnerId = 99;
        $postId = 1;

        $commenter = $this->createMock(User::class);

        $postOwnerParameters = $this->createMock(\App\Entity\UserParameter::class);
        $postOwnerParameters->method('getNotifNewComment')->willReturn(false);

        $postOwner = $this->createMock(User::class);
        $postOwner->method('getParameters')->willReturn($postOwnerParameters);

        $post = $this->createMock(Post::class);
        $post->method('getUser')->willReturn($postOwner);

        $this->postRepository->method('find')->with($postId)->willReturn($post);
        $this->userRepository->method('find')->with($commenterId)->willReturn($commenter);

        $message = new CommentCreatedMessage($postId, $commenterId);

        $this->pushNotificationService->expects($this->never())
            ->method('sendToUser')
        ;

        ($this->handler)($message);
    }

    public function testInvokeDoesNothingIfEntityNotFound(): void
    {
        $this->postRepository->method('find')->willReturn(null);
        $this->userRepository->method('find')->willReturn(null);

        $message = new CommentCreatedMessage(1, 1);

        $this->pushNotificationService->expects($this->never())
            ->method('sendToUser')
        ;

        ($this->handler)($message);
    }
}
