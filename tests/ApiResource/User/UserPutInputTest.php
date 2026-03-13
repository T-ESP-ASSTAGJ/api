<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\User;

use App\ApiResource\User\UserPatchInput;
use PHPUnit\Framework\TestCase;

class UserPutInputTest extends TestCase
{
    public function testPublicProperties(): void
    {
        $input = new UserPatchInput();
        $input->username = 'johndoe';
        $input->phoneNumber = '+33123456789';
        $input->profilePicture = 'https://example.com/profile.jpg';
        $input->bio = 'Music lover and software developer';

        $this->assertSame('johndoe', $input->username);
        $this->assertSame('+33123456789', $input->phoneNumber);
        $this->assertSame('https://example.com/profile.jpg', $input->profilePicture);
        $this->assertSame('Music lover and software developer', $input->bio);
    }

    public function testDefaultValuesAreNull(): void
    {
        $input = new UserPatchInput();

        $this->assertNull($input->username);
        $this->assertNull($input->phoneNumber);
        $this->assertNull($input->profilePicture);
        $this->assertNull($input->bio);
    }

    public function testCanSetFieldsToNull(): void
    {
        $input = new UserPatchInput();
        $input->username = null;

        $this->assertNull($input->username);
    }
}
