<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Follow;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $user = new User();

        $this->assertNull($user->getId());

        $result = $user->setUsername('testuser');
        $this->assertSame($user, $result);
        $this->assertSame('testuser', $user->getUsername());

        $result = $user->setEmail('test@example.com');
        $this->assertSame($user, $result);
        $this->assertSame('test@example.com', $user->getEmail());

        $result = $user->setPassword('hashedpassword');
        $this->assertSame($user, $result);
        $this->assertSame('hashedpassword', $user->getPassword());

        $result = $user->setPhoneNumber('+33123456789');
        $this->assertSame($user, $result);
        $this->assertSame('+33123456789', $user->getPhoneNumber());

        $result = $user->setProfilePicture('https://example.com/profile.jpg');
        $this->assertSame($user, $result);
        $this->assertSame('https://example.com/profile.jpg', $user->getProfilePicture());

        $result = $user->setBio('This is my bio');
        $this->assertSame($user, $result);
        $this->assertSame('This is my bio', $user->getBio());

        $result = $user->setIsVerified(true);
        $this->assertSame($user, $result);
        $this->assertTrue($user->getIsVerified());

        $result = $user->setNeedsProfile(false);
        $this->assertSame($user, $result);
        $this->assertFalse($user->getNeedsProfile());
    }

    public function testRoles(): void
    {
        $user = new User();

        $this->assertSame([], $user->getRoles());

        $roles = ['ROLE_USER', 'ROLE_ADMIN'];
        $result = $user->setRoles($roles);
        $this->assertSame($user, $result);
        $this->assertSame($roles, $user->getRoles());
    }

    public function testUserIdentifier(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        $this->assertSame('test@example.com', $user->getUserIdentifier());
    }

    public function testDefaultValues(): void
    {
        $user = new User();

        $this->assertNull($user->getUsername());
        $this->assertNull($user->getProfilePicture());
        $this->assertNull($user->getBio());
        $this->assertFalse($user->getIsVerified());
        $this->assertTrue($user->getNeedsProfile());
        $this->assertSame([], $user->getRoles());
    }

    public function testFollowingAndFollowersCollections(): void
    {
        $user = new User();

        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $user->getFollowing());
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $user->getFollowers());
        $this->assertCount(0, $user->getFollowing());
        $this->assertCount(0, $user->getFollowers());
    }

    public function testFollowingCount(): void
    {
        $user = new User();

        $this->assertSame(0, $user->getFollowingCount());
    }

    public function testFollowersCount(): void
    {
        $user = new User();

        $this->assertSame(0, $user->getFollowersCount());
    }

    public function testOnPrePersistSetsDefaultRole(): void
    {
        $user = new User();

        $this->assertSame([], $user->getRoles());

        $user->onPrePersist();

        $this->assertSame(['ROLE_USER'], $user->getRoles());
    }

    public function testOnPrePersistDoesNotOverrideExistingRoles(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        $user->onPrePersist();

        $this->assertSame(['ROLE_ADMIN'], $user->getRoles());
    }

    public function testTimeStampableTrait(): void
    {
        $user = new User();
        $user->setCreatedAt();

        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getUpdatedAt());
    }

    public function testSerialize(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('secret');

        $serialized = $user->__serialize();

        $this->assertIsArray($serialized);
        // Password should be hashed in serialization
        $this->assertNotSame('secret', $serialized["\0App\Entity\User\0password"]);
    }

    public function testEraseCredentials(): void
    {
        $user = new User();

        // This method is deprecated and does nothing, just verify it exists and doesn't throw
        $user->eraseCredentials();

        $this->assertTrue(true); // If we reach here, the method exists and didn't throw
    }
}
