<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\ApiResource\User\UserFollowOutput;
use App\Entity\Follow;
use App\Factory\UserFactory;
use App\Repository\FollowRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class FollowRepositoryTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    private EntityManagerInterface $em;

    private FollowRepository $repo;

    protected function setUp(): void
    {
        $container = static::getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->repo = $container->get(FollowRepository::class);
    }

    public function testFindFollowersReturnsFollowersOfUser(): void
    {
        $followed = UserFactory::createOne();
        $follower = UserFactory::createOne();

        $follow = (new Follow())->setFollower($follower)->setFollowedUser($followed);
        $this->em->persist($follow);
        $this->em->flush();

        $results = $this->repo->findFollowers($followed->getId());

        $this->assertCount(1, $results);
        $this->assertInstanceOf(UserFollowOutput::class, $results[0]);
        $this->assertSame($follower->getId(), $results[0]->id);
        $this->assertSame($follower->getUsername(), $results[0]->username);
    }

    public function testFindFollowersReturnsEmptyWhenNoFollowers(): void
    {
        $user = UserFactory::createOne();

        $this->assertSame([], $this->repo->findFollowers($user->getId()));
    }

    public function testFindFollowingReturnsUsersFollowedByUser(): void
    {
        $follower = UserFactory::createOne();
        $followed = UserFactory::createOne();

        $follow = (new Follow())->setFollower($follower)->setFollowedUser($followed);
        $this->em->persist($follow);
        $this->em->flush();

        $results = $this->repo->findFollowing($follower->getId());

        $this->assertCount(1, $results);
        $this->assertInstanceOf(UserFollowOutput::class, $results[0]);
        $this->assertSame($followed->getId(), $results[0]->id);
        $this->assertSame($followed->getUsername(), $results[0]->username);
    }

    public function testFindFollowingReturnsEmptyWhenNotFollowingAnyone(): void
    {
        $user = UserFactory::createOne();

        $this->assertSame([], $this->repo->findFollowing($user->getId()));
    }
}
