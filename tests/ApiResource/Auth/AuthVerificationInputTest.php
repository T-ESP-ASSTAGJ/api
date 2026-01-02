<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Auth;

use App\ApiResource\Auth\AuthVerificationInput;
use PHPUnit\Framework\TestCase;

class AuthVerificationInputTest extends TestCase
{
    public function testConstructorWithAllParameters(): void
    {
        $input = new AuthVerificationInput('test@example.com', '123456');

        $this->assertSame('test@example.com', $input->email);
        $this->assertSame('123456', $input->code);
    }

    public function testConstructorWithDefaultValues(): void
    {
        $input = new AuthVerificationInput();

        $this->assertNull($input->email);
        $this->assertNull($input->code);
    }

    public function testConstructorWithPartialParameters(): void
    {
        $input = new AuthVerificationInput('user@test.com');

        $this->assertSame('user@test.com', $input->email);
        $this->assertNull($input->code);
    }
}
