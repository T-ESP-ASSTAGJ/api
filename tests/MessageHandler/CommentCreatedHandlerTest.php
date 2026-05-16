<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\Entity\Enum\VisibilityEnum;
use App\Entity\Post;
use App\Entity\User;
use App\Message\CommentCreatedMessage;
use App\MessageHandler\CommentCreatedHandler;
use App\Repository\FollowRepository;
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

    /**
     * @var FollowRepository&MockObject
     */
    private FollowRepository $followRepository;

    private CommentCreatedHandler $handler;

    protected function setUp(): void
    {
        $this->pushNotificationService = $this->createMock(PushNotificationService::class);
        $this->postRepository = $this->createMock(PostRepository::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->followRepository = $this->createMock(FollowRepository::class);

        $this->handler = new CommentCreatedHandler(
            $this->pushNotificationService,
            $this->postRepository,
            $this->userRepository,
            $this->followRepository,
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
        $postOwnerParameters->method('getNotifNewComment')->willReturn(VisibilityEnum::Public);

        $postOwner = $this->createMock(User::class);
        $postOwner->method('getId')->willReturn($postOwnerId);
        $postOwner->method('getParameters')->willReturn($postOwnerParameters);

        $post = $this->createMock(Post::class);
        $post->method('getUser')->willReturn($postOwner);

        $this->postRepository->method('find')->with($postId)->willReturn($post);
        $this->userRepository->method('find')->with($commenterId)->willReturn($commenter);

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

        ($this->handler)(new CommentCreatedMessage($postId, $commenterId));
    }

    public function testInvokeDoesNotSendPushNotificationIfPrivate(): void
    {
        $postOwnerParameters = $this->createMock(\App\Entity\UserParameter::class);
        $postOwnerParameters->method('getNotifNewComment')->willReturn(VisibilityEnum::Private);

        $postOwner = $this->createMock(User::class);
        $postOwner->method('getParameters')->willReturn($postOwnerParameters);

        $post = $this->createMock(Post::class);
        $post->method('getUser')->willReturn($postOwner);

        $commenter = $this->createMock(User::class);

        $this->postRepository->method('find')->with(1)->willReturn($post);
        $this->userRepository->method('find')->with(10)->willReturn($commenter);

        $this->pushNotificationService->expects($this->never())->method('sendToUser');

        ($this->handler)(new CommentCreatedMessage(1, 10));
    }

    public function testInvokeSendsNotificationIfFriendsAndMutualFollow(): void
    {
        $commenterId = 10;
        $postOwnerId = 99;

        $commenter = $this->createMock(User::class);
        $commenter->method('getId')->willReturn($commenterId);
        $commenter->method('getUsername')->willReturn('JaneDoe');
        $commenter->method('getProfilePicture')->willReturn('pic.jpg');

        $postOwnerParameters = $this->createMock(\App\Entity\UserParameter::class);
        $postOwnerParameters->method('getNotifNewComment')->willReturn(VisibilityEnum::Friends);

        $postOwner = $this->createMock(User::class);
        $postOwner->method('getId')->willReturn($postOwnerId);
        $postOwner->method('getParameters')->willReturn($postOwnerParameters);

        $post = $this->createMock(Post::class);
        $post->method('getUser')->willReturn($postOwner);

        $this->postRepository->method('find')->with(1)->willReturn($post);
        $this->userRepository->method('find')->with($commenterId)->willReturn($commenter);
        $this->followRepository->method('isMutualFollow')->with($commenterId, $postOwnerId)->willReturn(true);

        $this->pushNotificationService->expects($this->once())->method('sendToUser');

        ($this->handler)(new CommentCreatedMessage(1, $commenterId));
    }

    public function testInvokeDoesNotSendNotificationIfFriendsAndNotMutualFollow(): void
    {
        $commenterId = 10;
        $postOwnerId = 99;

        $commenter = $this->createMock(User::class);
        $commenter->method('getId')->willReturn($commenterId);

        $postOwnerParameters = $this->createMock(\App\Entity\UserParameter::class);
        $postOwnerParameters->method('getNotifNewComment')->willReturn(VisibilityEnum::Friends);

        $postOwner = $this->createMock(User::class);
        $postOwner->method('getId')->willReturn($postOwnerId);
        $postOwner->method('getParameters')->willReturn($postOwnerParameters);

        $post = $this->createMock(Post::class);
        $post->method('getUser')->willReturn($postOwner);

        $this->postRepository->method('find')->with(1)->willReturn($post);
        $this->userRepository->method('find')->with($commenterId)->willReturn($commenter);
        $this->followRepository->method('isMutualFollow')->with($commenterId, $postOwnerId)->willReturn(false);

        $this->pushNotificationService->expects($this->never())->method('sendToUser');

        ($this->handler)(new CommentCreatedMessage(1, $commenterId));
    }

    public function testInvokeDoesNothingIfEntityNotFound(): void
    {
        $this->postRepository->method('find')->willReturn(null);
        $this->userRepository->method('find')->willReturn(null);

        $this->pushNotificationService->expects($this->never())->method('sendToUser');

        ($this->handler)(new CommentCreatedMessage(1, 1));
    }
}
