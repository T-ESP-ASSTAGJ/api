<?php

declare(strict_types=1);

namespace App\Tests\State\Comment;

use ApiPlatform\Metadata\Post as PostOperation;
use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\Comment\CommentCreateInput;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\State\Comment\CommentCreateProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CommentCreateProcessorTest extends TestCase
{
    /**
     * @var EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private EntityManagerInterface $em;

    /**
     * @var ValidatorInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private ValidatorInterface $validator;

    /**
     * @var Security&\PHPUnit\Framework\MockObject\MockObject
     */
    private Security $security;

    /**
     * @var MessageBusInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private MessageBusInterface $bus;

    private CommentCreateProcessor $processor;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->processor = new CommentCreateProcessor($this->em, $this->validator, $this->security, $this->bus);
    }

    public function testThrowsWhenPostIdMissing(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $input = new CommentCreateInput();
        $input->content = 'test';
        $this->processor->process($input, new PostOperation(), []);
    }

    public function testThrowsWhenPostNotFound(): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn(null);
        $this->em->method('getRepository')->willReturn($repo);

        $input = new CommentCreateInput();
        $input->content = 'test';

        $this->expectException(NotFoundHttpException::class);
        $this->processor->process($input, new PostOperation(), ['postId' => 999]);
    }

    public function testThrowsWhenUnauthenticated(): void
    {
        $post = $this->makePost(1);
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn($post);
        $this->em->method('getRepository')->willReturn($repo);
        $this->security->method('getUser')->willReturn(null);

        $input = new CommentCreateInput();
        $input->content = 'test';

        $this->expectException(\RuntimeException::class);
        $this->processor->process($input, new PostOperation(), ['postId' => 1]);
    }

    public function testCreatesComment(): void
    {
        $post = $this->makePost(5);
        $user = $this->makeUser(42);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn($post);
        $this->em->method('getRepository')->willReturn($repo);
        $this->security->method('getUser')->willReturn($user);
        $this->validator->method('validate')->willReturn(new ConstraintViolationList());
        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');
        $this->bus->expects($this->once())->method('dispatch')
            ->willReturn(new \Symfony\Component\Messenger\Envelope(new \stdClass()))
        ;

        $input = new CommentCreateInput();
        $input->content = 'Great song!';

        $result = $this->processor->process($input, new PostOperation(), ['postId' => 5]);

        $this->assertInstanceOf(Comment::class, $result);
        $this->assertSame('Great song!', $result->getContent());
    }

    public function testThrowsOnValidationFailure(): void
    {
        $post = $this->makePost(5);
        $user = $this->makeUser(42);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn($post);
        $this->em->method('getRepository')->willReturn($repo);
        $this->security->method('getUser')->willReturn($user);

        $violations = $this->createMock(ConstraintViolationListInterface::class);
        $violations->method('count')->willReturn(1);
        $this->validator->method('validate')->willReturn($violations);

        $input = new CommentCreateInput();
        $input->content = '';

        $this->expectException(ValidationException::class);
        $this->processor->process($input, new PostOperation(), ['postId' => 5]);
    }

    private function makeUser(int $id): User
    {
        $user = new User();
        $ref = new \ReflectionProperty(User::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($user, $id);

        return $user;
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
