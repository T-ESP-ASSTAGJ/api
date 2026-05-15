<?php

declare(strict_types=1);

namespace App\Tests\State\Post;

use ApiPlatform\Metadata\Post as PostOperation;
use App\Entity\Post;
use App\Entity\User;
use App\Message\PersistPostViewsMessage;
use App\Service\Post\PostViewCounter;
use App\State\Post\PostViewIncrementProcessor;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class PostViewIncrementProcessorTest extends TestCase
{
    /**
     * @var PostViewCounter&\PHPUnit\Framework\MockObject\MockObject
     */
    private PostViewCounter $viewCounter;

    /**
     * @var Security&\PHPUnit\Framework\MockObject\MockObject
     */
    private Security $security;

    /**
     * @var MessageBusInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private MessageBusInterface $bus;

    private PostViewIncrementProcessor $processor;

    protected function setUp(): void
    {
        $this->viewCounter = $this->createMock(PostViewCounter::class);
        $this->security = $this->createMock(Security::class);
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->processor = new PostViewIncrementProcessor(
            $this->viewCounter,
            $this->security,
            $this->bus,
        );
    }

    public function testThrowsWhenDataIsNotPost(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->processor->process(new \stdClass(), new PostOperation()); // @phpstan-ignore argument.type
    }

    public function testReturnsPostWithoutIncrementWhenIdIsNull(): void
    {
        $post = new Post();
        // id is null

        $this->viewCounter->expects($this->never())->method('increment');
        $this->bus->expects($this->never())->method('dispatch');

        $result = $this->processor->process($post, new PostOperation());

        $this->assertSame($post, $result);
    }

    public function testIncrementsViewWhenUserIsAuthenticated(): void
    {
        $post = $this->makePost(1);
        $user = new User();
        $ref = new \ReflectionProperty(User::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($user, 42);

        $this->security->method('getUser')->willReturn($user);
        $this->viewCounter->expects($this->once())->method('increment')->with($post, 42);
        $this->viewCounter->method('getViews')->willReturn(10);
        $this->bus->expects($this->once())->method('dispatch')
            ->with($this->isInstanceOf(PersistPostViewsMessage::class))
            ->willReturn(new Envelope(new PersistPostViewsMessage(1)))
        ;

        $result = $this->processor->process($post, new PostOperation());

        $this->assertSame(10, $result->getViewsCount());
    }

    public function testDoesNotIncrementWhenUnauthenticated(): void
    {
        $post = $this->makePost(1);
        $this->security->method('getUser')->willReturn(null);
        $this->viewCounter->expects($this->never())->method('increment');
        $this->viewCounter->method('getViews')->willReturn(5);
        $this->bus->method('dispatch')->willReturn(new Envelope(new PersistPostViewsMessage(1)));

        $result = $this->processor->process($post, new PostOperation());

        $this->assertSame(5, $result->getViewsCount());
    }

    private function makePost(int $id): Post
    {
        $post = new Post();
        $ref = new \ReflectionProperty(Post::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($post, $id);

        return $post;
    }
}
