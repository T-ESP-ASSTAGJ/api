<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\Entity\Post;
use App\Message\PersistPostViewsMessage;
use App\MessageHandler\PersistPostViewsMessageHandler;
use App\Service\Post\PostViewsPersistenceServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PersistPostViewsMessageHandlerTest extends TestCase
{
    /**
     * @var EntityManagerInterface&MockObject
     */
    private EntityManagerInterface $entityManager;

    private PersistPostViewsMessageHandler $handler;

    /**
     * @var PostViewsPersistenceServiceInterface&MockObject
     */
    private PostViewsPersistenceServiceInterface $postViewsPersistenceService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->postViewsPersistenceService = $this->createMock(PostViewsPersistenceServiceInterface::class);

        $this->handler = new PersistPostViewsMessageHandler(
            $this->entityManager,
            $this->postViewsPersistenceService,
        );
    }

    public function testInvokePersistsViewsWhenPostExists(): void
    {
        $postId = 123;
        $message = new PersistPostViewsMessage($postId);

        $post = $this->createMock(Post::class);
        $post->method('getId')->willReturn($postId);

        $this->entityManager
            ->expects($this->once())
            ->method('find')
            ->with(Post::class, $postId)
            ->willReturn($post)
        ;

        $this->postViewsPersistenceService
            ->expects($this->once())
            ->method('persistViews')
            ->with($postId)
        ;

        ($this->handler)($message);
    }

    public function testInvokeDoesNothingWhenPostDoesNotExist(): void
    {
        $postId = 999;
        $message = new PersistPostViewsMessage($postId);

        $this->entityManager
            ->expects($this->once())
            ->method('find')
            ->with(Post::class, $postId)
            ->willReturn(null)
        ;

        $this->postViewsPersistenceService
            ->expects($this->never())
            ->method('persistViews')
        ;

        ($this->handler)($message);
    }
}
