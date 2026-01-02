<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Auth;

use App\ApiResource\Auth\AuthVerificationOutput;
use PHPUnit\Framework\TestCase;

class AuthVerificationOutputTest extends TestCase
{
    public function testPublicProperties(): void
    {
        $output = new AuthVerificationOutput();
        $output->message = 'Authentication successful';
        $output->needsProfile = true;
        $output->isVerified = true;
        $output->token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9';

        $this->assertSame('Authentication successful', $output->message);
        $this->assertTrue($output->needsProfile);
        $this->assertTrue($output->isVerified);
        $this->assertSame('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9', $output->token);
    }

    public function testDefaultNullableValues(): void
    {
        $output = new AuthVerificationOutput();

        $this->assertNull($output->needsProfile);
        $this->assertNull($output->token);
    }

    public function testCanSetIsVerifiedToFalse(): void
    {
        $output = new AuthVerificationOutput();
        $output->isVerified = false;

        $this->assertFalse($output->isVerified);
    }

    public function testCanSetNeedsProfileToFalse(): void
    {
        $output = new AuthVerificationOutput();
        $output->needsProfile = false;

        $this->assertFalse($output->needsProfile);
    }
}
