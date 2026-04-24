<?php

declare(strict_types=1);

namespace App\Tests\Entity;

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

    public function testSetIsFollowersPublic(): void
    {
        $this->userParameter->setIsFollowersPublic(false);
        self::assertFalse($this->userParameter->getIsFollowersPublic());
    }

    public function testSetIsFollowingPublic(): void
    {
        $this->userParameter->setIsFollowingPublic(false);
        self::assertFalse($this->userParameter->getIsFollowingPublic());
    }

    public function testSetIsStatsPublic(): void
    {
        $this->userParameter->setIsStatsPublic(false);
        self::assertFalse($this->userParameter->getIsStatsPublic());
    }

    public function testSetIsPlaylistPublic(): void
    {
        $this->userParameter->setIsPlaylistPublic(false);
        self::assertFalse($this->userParameter->getIsPlaylistPublic());
    }

    public function testSetIsLikesPublic(): void
    {
        $this->userParameter->setIsLikesPublic(false);
        self::assertFalse($this->userParameter->getIsLikesPublic());
    }

    public function testSetNotifNewFollower(): void
    {
        $this->userParameter->setNotifNewFollower(false);
        self::assertFalse($this->userParameter->getNotifNewFollower());
    }

    public function testSetNotifNewLike(): void
    {
        $this->userParameter->setNotifNewLike(false);
        self::assertFalse($this->userParameter->getNotifNewLike());
    }

    public function testSetNotifNewComment(): void
    {
        $this->userParameter->setNotifNewComment(false);
        self::assertFalse($this->userParameter->getNotifNewComment());
    }

    public function testSetNotifNewMessage(): void
    {
        $this->userParameter->setNotifNewMessage(false);
        self::assertFalse($this->userParameter->getNotifNewMessage());
    }
}
