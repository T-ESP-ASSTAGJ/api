<?php

declare(strict_types=1);

namespace App\Tests\State\User;

use ApiPlatform\Metadata\Get;
use App\ApiResource\User\UserFollowOutput;
use App\Entity\User;
use App\Repository\FollowRepository;
use App\State\User\UserFollowersProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class UserFollowersProviderTest extends TestCase
{
    /**
     * @var EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private EntityManagerInterface $em;

    /**
     * @var FollowRepository&\PHPUnit\Framework\MockObject\MockObject
     */
    private FollowRepository $followRepository;

    private UserFollowersProvider $provider;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->followRepository = $this->createMock(FollowRepository::class);
        $this->provider = new UserFollowersProvider($this->em, $this->followRepository);
    }

    public function testReturnsFollowers(): void
    {
        $user = new User();
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn($user);
        $this->em->method('getRepository')->willReturn($repo);

        $followers = [new UserFollowOutput(1, 'alice', null)];
        $this->followRepository->method('findFollowers')->with(42)->willReturn($followers);

        $result = $this->provider->provide(new Get(), ['id' => 42]);

        $this->assertSame($followers, $result);
    }

    public function testThrowsWhenUserNotFound(): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn(null);
        $this->em->method('getRepository')->willReturn($repo);

        $this->expectException(UnauthorizedHttpException::class);
        $this->provider->provide(new Get(), ['id' => 999]);
    }
}
