<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\Entity\Like;
use App\Entity\Post;
use App\Entity\User;
use App\Message\LikeCreatedMessage;
use App\MessageHandler\LikeCreatedHandler;
use App\Repository\LikeRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\PushNotificationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LikeCreatedHandlerTest extends TestCase
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
     * @var LikeRepository&MockObject
     */
    private LikeRepository $likeRepository;

    /**
     * @var PostRepository&MockObject
     */
    private PostRepository $postRepository;

    private LikeCreatedHandler $handler;

    protected function setUp(): void
    {
        $this->pushNotificationService = $this->createMock(PushNotificationService::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->likeRepository = $this->createMock(LikeRepository::class);
        $this->postRepository = $this->createMock(PostRepository::class);

        $this->handler = new LikeCreatedHandler(
            $this->pushNotificationService,
            $this->userRepository,
            $this->likeRepository,
            $this->postRepository,
        );
    }

    public function testInvokeSendsPushNotificationSuccessfully(): void
    {
        $likerId = 1;
        $ownerId = 99;
        $likeId = 777;
        $postId = 1;

        $liker = $this->createMock(User::class);
        $liker->method('getUsername')->willReturn('JaneDoe');
        $liker->method('getId')->willReturn($likerId);
        $liker->method('getProfilePicture')->willReturn('pic.jpg');

        $ownerParameters = $this->createMock(\App\Entity\UserParameter::class);
        $ownerParameters->method('getNotifNewLike')->willReturn(true);

        $owner = $this->createMock(User::class);
        $owner->method('getId')->willReturn($ownerId);
        $owner->method('getParameters')->willReturn($ownerParameters);

        $like = $this->createMock(Like::class);
        $like->method('getId')->willReturn($likeId);
        $like->method('getEntityClassLabel')->willReturn('post');
        $like->method('getEntityClass')->willReturn(Post::class);
        $like->method('getEntityId')->willReturn(456);

        $content = $this->createMock(Post::class);
        $content->method('getId')->willReturn($postId);
        $content->method('getFrontImage')->willReturn('https://toto.fr/image');

        $this->userRepository->method('find')->willReturnMap([
            [$likerId, null, null, $liker],
            [$ownerId, null, null, $owner],
        ]);
        $this->likeRepository->method('find')->with($likeId)->willReturn($like);
        $this->postRepository->method('find')->with($postId)->willReturn($content);

        $message = new LikeCreatedMessage($likerId, $ownerId, $likeId, $postId);

        $this->pushNotificationService->expects($this->once())
            ->method('sendToUser')
            ->with(
                $ownerId,
                'JaneDoe',
                'has liked your post',
                [
                    'postId' => $postId,
                    'entityClass' => Post::class,
                    'entityId' => '456',
                    'profilePicture' => 'pic.jpg',
                    'postImage' => 'https://toto.fr/image',
                ],
            )
        ;

        ($this->handler)($message);
    }

    public function testInvokeDoesNotSendPushNotificationIfDisabled(): void
    {
        $likerId = 1;
        $ownerId = 99;
        $likeId = 777;
        $postId = 1;

        $liker = $this->createMock(User::class);
        $ownerParameters = $this->createMock(\App\Entity\UserParameter::class);
        $ownerParameters->method('getNotifNewLike')->willReturn(false);

        $owner = $this->createMock(User::class);
        $owner->method('getParameters')->willReturn($ownerParameters);

        $like = $this->createMock(Like::class);
        $content = $this->createMock(Post::class);

        $this->userRepository->method('find')->willReturnMap([
            [$likerId, null, null, $liker],
            [$ownerId, null, null, $owner],
        ]);
        $this->likeRepository->method('find')->with($likeId)->willReturn($like);
        $this->postRepository->method('find')->with($postId)->willReturn($content);

        $message = new LikeCreatedMessage($likerId, $ownerId, $likeId, $postId);

        $this->pushNotificationService->expects($this->never())
            ->method('sendToUser')
        ;

        ($this->handler)($message);
    }

    public function testInvokeDoesNothingIfEntityNotFound(): void
    {
        $this->userRepository->method('find')->willReturn(null);
        $this->likeRepository->method('find')->willReturn(null);
        $this->postRepository->method('find')->willReturn(null);

        $message = new LikeCreatedMessage(1, 99, 777, 1);

        $this->pushNotificationService->expects($this->never())
            ->method('sendToUser')
        ;

        ($this->handler)($message);
    }
}
