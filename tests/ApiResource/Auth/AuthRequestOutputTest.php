<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Auth;

use App\ApiResource\Auth\AuthRequestOutput;
use PHPUnit\Framework\TestCase;

class AuthRequestOutputTest extends TestCase
{
    public function testPublicProperties(): void
    {
        $output = new AuthRequestOutput();
        $output->message = 'Verification code sent successfully';

        $this->assertSame('Verification code sent successfully', $output->message);
    }

    public function testCanSetMessage(): void
    {
        $output = new AuthRequestOutput();
        $output->message = 'Code sent to your email';

        $this->assertSame('Code sent to your email', $output->message);
    }
}
