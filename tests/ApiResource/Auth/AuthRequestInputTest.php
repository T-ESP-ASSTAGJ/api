<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Auth;

use App\ApiResource\Auth\AuthRequestInput;
use PHPUnit\Framework\TestCase;

class AuthRequestInputTest extends TestCase
{
    public function testConstructor(): void
    {
        $input = new AuthRequestInput('test@example.com');

        $this->assertSame('test@example.com', $input->email);
    }

    public function testIsReadonly(): void
    {
        $input = new AuthRequestInput('user@test.com');

        $this->assertSame('user@test.com', $input->email);
    }
}
