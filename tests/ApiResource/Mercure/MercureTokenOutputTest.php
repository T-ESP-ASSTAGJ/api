<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Mercure;

use App\ApiResource\Mercure\MercureTokenOutput;
use PHPUnit\Framework\TestCase;

class MercureTokenOutputTest extends TestCase
{
    public function testDefaultTopicsIsEmptyArray(): void
    {
        $output = new MercureTokenOutput();

        $this->assertSame([], $output->topics);
    }

    public function testCanSetToken(): void
    {
        $output = new MercureTokenOutput();
        $output->token = 'eyJhbGciOiJIUzI1NiJ9.test.signature';

        $this->assertSame('eyJhbGciOiJIUzI1NiJ9.test.signature', $output->token);
    }

    public function testCanSetTopics(): void
    {
        $output = new MercureTokenOutput();
        $output->topics = ['/user/1', '/conversation/42'];

        $this->assertSame(['/user/1', '/conversation/42'], $output->topics);
    }
}
