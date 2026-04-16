<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\Entity\Like;
use App\Entity\Post;
use App\Entity\User;
use App\Message\LikeCreatedMessage;
use App\MessageHandler\LikeCreatedHandler;
use App\Service\PushNotificationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LikeCreatedHandlerTest extends TestCase
{
    /** @var PushNotificationService&MockObject */
    private PushNotificationService $pushNotificationService;

    private LikeCreatedHandler $handler;

    protected function setUp(): void
    {
        $this->pushNotificationService = $this->createMock(PushNotificationService::class);

        $this->handler = new LikeCreatedHandler(
            $this->pushNotificationService
        );
    }

    public function testInvokeSendsPushNotificationSuccessfully(): void
    {
        $liker = $this->createMock(User::class);
        $liker->method('getUsername')->willReturn('JaneDoe');
        $liker->method('getId')->willReturn(1);
        $liker->method('getProfilePicture')->willReturn('pic.jpg');

        $owner = $this->createMock(User::class);
        $owner->method('getId')->willReturn(99);

        $like = $this->createMock(Like::class);
        $like->method('getEntityClassLabel')->willReturn('post');
        $like->method('getEntityClass')->willReturn(Post::class);
        $like->method('getEntityId')->willReturn(456);

        $content = $this->createMock(Post::class);
        $content->method('getId')->willReturn(1);
        $content->method('getFrontImage')->willReturn('https://toto.fr/image');

        $message = new LikeCreatedMessage($liker, $owner, $like, $content);

        $this->pushNotificationService->expects($this->once())
            ->method('sendToUser')
            ->with(
                99,
                'JaneDoe',
                'has liked your post',
                [
                    'postId' => 1,
                    'entityClass' => Post::class,
                    'entityId' => '456',
                    'profilePicture' => 'pic.jpg',
                    'postImage' => 'https://toto.fr/image',
                ]
            );

        ($this->handler)($message);
    }
}
