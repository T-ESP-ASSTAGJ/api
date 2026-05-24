<?php

declare(strict_types=1);

namespace App\Tests\State\Like;

use ApiPlatform\Metadata\Post;
use App\ApiResource\Like\LikeCreateInput;
use App\Entity\Comment;
use App\Entity\Enum\LikeableTypeEnum;
use App\Entity\Post as PostEntity;
use App\Entity\User;
use App\State\Like\LikeCreateProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

class LikeCreateProcessorTest extends TestCase
{
    /**
     * @var EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private EntityManagerInterface $em;

    /**
     * @var Security&\PHPUnit\Framework\MockObject\MockObject
     */
    private Security $security;

    /**
     * @var MessageBusInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private MessageBusInterface $bus;

    private LikeCreateProcessor $processor;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->processor = new LikeCreateProcessor($this->em, $this->security, $this->bus);
    }

    public function testThrowsWhenEntityNotFound(): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn(null);
        $this->em->method('getRepository')->willReturn($repo);

        $input = new LikeCreateInput();
        $input->entityClass = LikeableTypeEnum::Post;
        $input->entityId = 999;

        $this->expectException(NotFoundHttpException::class);
        $this->processor->process($input, new Post());
    }

    public function testLikesPost(): void
    {
        $liker = $this->makeUser(2);
        $owner = $this->makeUser(1);
        $this->security->method('getUser')->willReturn($liker);

        $post = $this->createMock(PostEntity::class);
        $post->method('getUser')->willReturn($owner);
        $post->method('getId')->willReturn(5);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn($post);
        $repo->method('findOneBy')->willReturn(null);
        $this->em->method('getRepository')->willReturn($repo);

        $this->em->expects($this->once())->method('persist')
            ->with($this->callback(function ($like) {
                $ref = new \ReflectionProperty(\App\Entity\Like::class, 'id');
                $ref->setAccessible(true);
                $ref->setValue($like, 99);

                return true;
            }))
        ;
        $this->em->expects($this->once())->method('flush');
        $this->bus->expects($this->once())->method('dispatch')
            ->willReturn(new \Symfony\Component\Messenger\Envelope(new \stdClass()))
        ;

        $input = new LikeCreateInput();
        $input->entityClass = LikeableTypeEnum::Post;
        $input->entityId = 5;

        $this->processor->process($input, new Post());
    }

    public function testLikesComment(): void
    {
        $liker = $this->makeUser(2);
        $owner = $this->makeUser(1);
        $this->security->method('getUser')->willReturn($liker);

        $post = $this->createMock(PostEntity::class);
        $post->method('getId')->willReturn(10);

        $comment = $this->createMock(Comment::class);
        $comment->method('getUser')->willReturn($owner);
        $comment->method('getPost')->willReturn($post);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn($comment);
        $repo->method('findOneBy')->willReturn(null);
        $this->em->method('getRepository')->willReturn($repo);

        $this->em->expects($this->once())->method('persist')
            ->with($this->callback(function ($like) {
                $ref = new \ReflectionProperty(\App\Entity\Like::class, 'id');
                $ref->setAccessible(true);
                $ref->setValue($like, 99);

                return true;
            }))
        ;
        $this->em->expects($this->once())->method('flush');
        $this->bus->expects($this->once())->method('dispatch')
            ->willReturn(new \Symfony\Component\Messenger\Envelope(new \stdClass()))
        ;

        $input = new LikeCreateInput();
        $input->entityClass = LikeableTypeEnum::Comment;
        $input->entityId = 3;

        $this->processor->process($input, new Post());
    }

    public function testThrowsOnDuplicateLike(): void
    {
        $liker = $this->makeUser(2);
        $owner = $this->makeUser(1);
        $this->security->method('getUser')->willReturn($liker);

        $post = $this->createMock(PostEntity::class);
        $post->method('getUser')->willReturn($owner);
        $post->method('getId')->willReturn(5);

        $existingLike = new \App\Entity\Like();

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn($post);
        $repo->method('findOneBy')->willReturn($existingLike);
        $this->em->method('getRepository')->willReturn($repo);

        $this->em->expects($this->never())->method('flush');

        $input = new LikeCreateInput();
        $input->entityClass = LikeableTypeEnum::Post;
        $input->entityId = 5;

        $this->expectException(BadRequestHttpException::class);
        $this->processor->process($input, new Post());
    }

    private function makeUser(int $id): User
    {
        $user = new User();
        $ref = new \ReflectionProperty(User::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($user, $id);

        return $user;
    }
}
