<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Follow;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class FollowTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $follow = new Follow();
        $follower = new User();
        $followedUser = new User();

        $this->assertNull($follow->getId());

        $result = $follow->setFollower($follower);
        $this->assertSame($follow, $result);
        $this->assertSame($follower, $follow->getFollower());

        $result = $follow->setFollowedUser($followedUser);
        $this->assertSame($follow, $result);
        $this->assertSame($followedUser, $follow->getFollowedUser());
    }

    public function testTimeStampableTrait(): void
    {
        $follow = new Follow();
        $follow->setCreatedAt();

        $this->assertInstanceOf(\DateTimeImmutable::class, $follow->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $follow->getUpdatedAt());
    }
}
