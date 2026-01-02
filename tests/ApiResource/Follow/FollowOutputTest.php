<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Follow;

use App\ApiResource\Follow\FollowOutput;
use PHPUnit\Framework\TestCase;

class FollowOutputTest extends TestCase
{
    public function testPublicProperties(): void
    {
        $output = new FollowOutput();
        $output->message = 'Successfully followed user';

        $this->assertSame('Successfully followed user', $output->message);
    }

    public function testCanSetMessage(): void
    {
        $output = new FollowOutput();
        $output->message = 'User unfollowed';

        $this->assertSame('User unfollowed', $output->message);
    }
}
