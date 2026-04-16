<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\Entity\Post;
use App\Entity\User;
use App\Message\CommentCreatedMessage;
use App\MessageHandler\CommentCreatedHandler;
use App\Service\PushNotificationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CommentCreatedHandlerTest extends TestCase
{
    /** @var PushNotificationService&MockObject */
    private PushNotificationService $pushNotificationService;

    private CommentCreatedHandler $handler;

    protected function setUp(): void
    {
        $this->pushNotificationService = $this->createMock(PushNotificationService::class);

        $this->handler = new CommentCreatedHandler(
            $this->pushNotificationService
        );
    }

    public function testInvokeSendsPushNotificationToPostOwner(): void
    {
        $commenterId = 10;
        $commenterUsername = 'JaneDoe';
        $commenterPic = 'https://example.com/jane.jpg';

        $postOwnerId = 99;

        $commenter = $this->createMock(User::class);
        $commenter->method('getId')->willReturn($commenterId);
        $commenter->method('getUsername')->willReturn($commenterUsername);
        $commenter->method('getProfilePicture')->willReturn($commenterPic);

        $postOwner = $this->createMock(User::class);
        $postOwner->method('getId')->willReturn($postOwnerId);

        $post = $this->createMock(Post::class);
        $post->method('getUser')->willReturn($postOwner);

        $message = new CommentCreatedMessage($post, $commenter);

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
                ]
            );

        ($this->handler)($message);
    }
}
