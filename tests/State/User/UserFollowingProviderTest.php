<?php

declare(strict_types=1);

namespace App\Tests\State\User;

use ApiPlatform\Metadata\Get;
use App\ApiResource\User\UserFollowOutput;
use App\Entity\User;
use App\Repository\FollowRepository;
use App\State\User\UserFollowingProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class UserFollowingProviderTest extends TestCase
{
    /**
     * @var EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private EntityManagerInterface $em;

    /**
     * @var FollowRepository&\PHPUnit\Framework\MockObject\MockObject
     */
    private FollowRepository $followRepository;

    private UserFollowingProvider $provider;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->followRepository = $this->createMock(FollowRepository::class);
        $this->provider = new UserFollowingProvider($this->em, $this->followRepository);
    }

    public function testReturnsFollowing(): void
    {
        $user = new User();
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn($user);
        $this->em->method('getRepository')->willReturn($repo);

        $following = [new UserFollowOutput(2, 'bob', null)];
        $this->followRepository->method('findFollowing')->with(42)->willReturn($following);

        $result = $this->provider->provide(new Get(), ['id' => 42]);

        $this->assertSame($following, $result);
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
