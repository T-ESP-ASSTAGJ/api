<?php

declare(strict_types=1);

namespace App\Tests\State\Follow;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use App\ApiResource\Follow\FollowOutput;
use App\Entity\Follow as FollowEntity;
use App\Entity\User;
use App\Message\FollowCreatedMessage;
use App\State\Follow\FollowProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

class FollowProcessorTest extends TestCase
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

    private FollowProcessor $processor;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->processor = new FollowProcessor($this->em, $this->security, $this->bus);
    }

    public function testThrowsWhenUnauthenticated(): void
    {
        $this->security->method('getUser')->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->processor->process(null, new Post(name: 'follow'), ['id' => 1]);
    }

    public function testThrowsWhenMissingUserId(): void
    {
        $this->security->method('getUser')->willReturn($this->makeUser(1));

        $this->expectException(BadRequestHttpException::class);
        $this->processor->process(null, new Post(name: 'follow'), []);
    }

    public function testThrowsWhenTargetUserNotFound(): void
    {
        $this->security->method('getUser')->willReturn($this->makeUser(1));
        $this->mockRepos(null, null);

        $this->expectException(NotFoundHttpException::class);
        $this->processor->process(null, new Post(name: 'follow'), ['id' => 999]);
    }

    public function testThrowsWhenFollowingSelf(): void
    {
        $user = $this->makeUser(1);
        $this->security->method('getUser')->willReturn($user);
        $this->mockRepos($user, null);

        $this->expectException(BadRequestHttpException::class);
        $this->processor->process(null, new Post(name: 'follow'), ['id' => 1]);
    }

    public function testFollowsUser(): void
    {
        $currentUser = $this->makeUser(1);
        $targetUser = $this->makeUser(2);
        $this->security->method('getUser')->willReturn($currentUser);
        $this->mockRepos($targetUser, null);

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');
        $this->bus->expects($this->once())->method('dispatch')
            ->with($this->isInstanceOf(FollowCreatedMessage::class))
            ->willReturn(new \Symfony\Component\Messenger\Envelope(new \stdClass()))
        ;

        $result = $this->processor->process(null, new Post(name: 'follow'), ['id' => 2]);

        $this->assertInstanceOf(FollowOutput::class, $result);
        $this->assertStringContainsString('followed', $result->message);
    }

    public function testThrowsWhenAlreadyFollowing(): void
    {
        $currentUser = $this->makeUser(1);
        $targetUser = $this->makeUser(2);
        $this->security->method('getUser')->willReturn($currentUser);
        $this->mockRepos($targetUser, new FollowEntity());

        $this->expectException(BadRequestHttpException::class);
        $this->processor->process(null, new Post(name: 'follow'), ['id' => 2]);
    }

    public function testUnfollowsUser(): void
    {
        $currentUser = $this->makeUser(1);
        $targetUser = $this->makeUser(2);
        $follow = new FollowEntity();
        $this->security->method('getUser')->willReturn($currentUser);
        $this->mockRepos($targetUser, $follow);

        $this->em->expects($this->once())->method('remove')->with($follow);
        $this->em->expects($this->once())->method('flush');

        $result = $this->processor->process(null, new Delete(name: 'unfollow'), ['id' => 2]);

        $this->assertInstanceOf(FollowOutput::class, $result);
        $this->assertStringContainsString('unfollowed', $result->message);
    }

    public function testThrowsWhenNotFollowing(): void
    {
        $currentUser = $this->makeUser(1);
        $targetUser = $this->makeUser(2);
        $this->security->method('getUser')->willReturn($currentUser);
        $this->mockRepos($targetUser, null);

        $this->expectException(BadRequestHttpException::class);
        $this->processor->process(null, new Delete(name: 'unfollow'), ['id' => 2]);
    }

    public function testThrowsOnUnsupportedOperation(): void
    {
        $currentUser = $this->makeUser(1);
        $targetUser = $this->makeUser(2);
        $this->security->method('getUser')->willReturn($currentUser);
        $this->mockRepos($targetUser, null);

        $this->expectException(\RuntimeException::class);
        $this->processor->process(null, new Post(name: 'unknown'), ['id' => 2]);
    }

    private function makeUser(int $id): User
    {
        $user = new User();
        $ref = new \ReflectionProperty(User::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($user, $id);

        return $user;
    }

    private function mockRepos(?User $targetUser, ?FollowEntity $existingFollow): void
    {
        $userRepo = $this->createMock(EntityRepository::class);
        $userRepo->method('find')->willReturn($targetUser);

        $followRepo = $this->createMock(EntityRepository::class);
        $followRepo->method('findOneBy')->willReturn($existingFollow);

        $this->em->method('getRepository')->willReturnMap([
            [User::class, $userRepo],
            [FollowEntity::class, $followRepo],
        ]);
    }
}
