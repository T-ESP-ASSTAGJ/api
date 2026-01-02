<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Spotify;

use App\ApiResource\Spotify\UserProfileDTO;
use PHPUnit\Framework\TestCase;

class UserProfileDTOTest extends TestCase
{
    public function testConstructor(): void
    {
        $userProfile = new UserProfileDTO(
            id: 'spotify-user-123',
            displayName: 'Test User',
            email: 'test@example.com',
            country: 'US',
            followers: 500,
            imageUrl: 'https://example.com/user.jpg',
            product: 'premium'
        );

        $this->assertSame('spotify-user-123', $userProfile->id);
        $this->assertSame('Test User', $userProfile->displayName);
        $this->assertSame('test@example.com', $userProfile->email);
        $this->assertSame('US', $userProfile->country);
        $this->assertSame(500, $userProfile->followers);
        $this->assertSame('https://example.com/user.jpg', $userProfile->imageUrl);
        $this->assertSame('premium', $userProfile->product);
    }

    public function testConstructorWithNullableValues(): void
    {
        $userProfile = new UserProfileDTO(
            id: 'spotify-user-456',
            displayName: 'Another User',
            email: null,
            country: 'FR',
            followers: 0,
            imageUrl: null,
            product: 'free'
        );

        $this->assertSame('spotify-user-456', $userProfile->id);
        $this->assertSame('Another User', $userProfile->displayName);
        $this->assertNull($userProfile->email);
        $this->assertSame('FR', $userProfile->country);
        $this->assertSame(0, $userProfile->followers);
        $this->assertNull($userProfile->imageUrl);
        $this->assertSame('free', $userProfile->product);
    }

    public function testReadonlyProperties(): void
    {
        $userProfile = new UserProfileDTO(
            id: 'test',
            displayName: 'test',
            email: 'test@example.com',
            country: 'US',
            followers: 100,
            imageUrl: null,
            product: 'premium'
        );

        $reflection = new \ReflectionClass($userProfile);
        $this->assertTrue($reflection->isReadOnly());
    }
}
