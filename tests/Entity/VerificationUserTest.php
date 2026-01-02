<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\VerificationUser;
use PHPUnit\Framework\TestCase;

class VerificationUserTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $verificationUser = new VerificationUser();

        $this->assertNull($verificationUser->getId());

        $result = $verificationUser->setEmail('test@example.com');
        $this->assertSame($verificationUser, $result);
        $this->assertSame('test@example.com', $verificationUser->getEmail());

        $result = $verificationUser->setCode('123456');
        $this->assertSame($verificationUser, $result);
        $this->assertSame('123456', $verificationUser->getCode());

        $expiresAt = new \DateTime('2025-12-31 23:59:59');
        $result = $verificationUser->setExpiresAt($expiresAt);
        $this->assertSame($verificationUser, $result);
        $this->assertSame($expiresAt, $verificationUser->getExpiresAt());
    }

    public function testDefaultValues(): void
    {
        $verificationUser = new VerificationUser();

        $this->assertNull($verificationUser->getEmail());
        $this->assertNull($verificationUser->getCode());
        $this->assertNull($verificationUser->getExpiresAt());
    }
}
