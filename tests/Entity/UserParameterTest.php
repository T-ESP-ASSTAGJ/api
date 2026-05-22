<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Enum\VisibilityEnum;
use App\Entity\User;
use App\Entity\UserParameter;
use App\Util\ReflectionUtil;
use PHPUnit\Framework\TestCase;

final class UserParameterTest extends TestCase
{
    private UserParameter $userParameter;

    protected function setUp(): void
    {
        $this->userParameter = new UserParameter();
    }

    public function testGetId(): void
    {
        ReflectionUtil::setPropertyValue($this->userParameter, 'id', 42);
        self::assertSame(42, $this->userParameter->getId());
    }

    public function testSetUser(): void
    {
        $user = $this->createMock(User::class);
        $this->userParameter->setUser($user);
        self::assertSame($user, $this->userParameter->getUser());
    }

    public function testSetFollowersVisibility(): void
    {
        $this->userParameter->setFollowersVisibility(VisibilityEnum::Friends);
        self::assertSame(VisibilityEnum::Friends, $this->userParameter->getFollowersVisibility());
    }

    public function testSetFollowingVisibility(): void
    {
        $this->userParameter->setFollowingVisibility(VisibilityEnum::Private);
        self::assertSame(VisibilityEnum::Private, $this->userParameter->getFollowingVisibility());
    }

    public function testSetStatsVisibility(): void
    {
        $this->userParameter->setStatsVisibility(VisibilityEnum::Friends);
        self::assertSame(VisibilityEnum::Friends, $this->userParameter->getStatsVisibility());
    }

    public function testSetPlaylistVisibility(): void
    {
        $this->userParameter->setPlaylistVisibility(VisibilityEnum::Private);
        self::assertSame(VisibilityEnum::Private, $this->userParameter->getPlaylistVisibility());
    }

    public function testSetLikesVisibility(): void
    {
        $this->userParameter->setLikesVisibility(VisibilityEnum::Friends);
        self::assertSame(VisibilityEnum::Friends, $this->userParameter->getLikesVisibility());
    }

    public function testSetNotifNewFollower(): void
    {
        $this->userParameter->setNotifNewFollower(true);
        self::assertTrue($this->userParameter->getNotifNewFollower());
    }

    public function testSetNotifNewLike(): void
    {
        $this->userParameter->setNotifNewLike(false);
        self::assertFalse($this->userParameter->getNotifNewLike());
    }

    public function testSetNotifNewComment(): void
    {
        $this->userParameter->setNotifNewComment(true);
        self::assertTrue($this->userParameter->getNotifNewComment());
    }

    public function testSetNotifNewMessage(): void
    {
        $this->userParameter->setNotifNewMessage(false);
        self::assertFalse($this->userParameter->getNotifNewMessage());
    }
}
