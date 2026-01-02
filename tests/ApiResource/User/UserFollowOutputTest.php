<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\User;

use App\ApiResource\User\UserFollowOutput;
use PHPUnit\Framework\TestCase;

class UserFollowOutputTest extends TestCase
{
    public function testConstructorWithAllValues(): void
    {
        $output = new UserFollowOutput(
            id: 1,
            username: 'testuser',
            profilePicture: 'https://example.com/profile.jpg'
        );

        $this->assertSame(1, $output->id);
        $this->assertSame('testuser', $output->username);
        $this->assertSame('https://example.com/profile.jpg', $output->profilePicture);
    }

    public function testConstructorWithNullValues(): void
    {
        $output = new UserFollowOutput(
            id: null,
            username: null,
            profilePicture: null
        );

        $this->assertNull($output->id);
        $this->assertNull($output->username);
        $this->assertNull($output->profilePicture);
    }

    public function testReadonlyProperties(): void
    {
        $output = new UserFollowOutput(
            id: 1,
            username: 'testuser',
            profilePicture: null
        );

        $reflection = new \ReflectionClass($output);
        $this->assertTrue($reflection->isReadOnly());
    }
}
