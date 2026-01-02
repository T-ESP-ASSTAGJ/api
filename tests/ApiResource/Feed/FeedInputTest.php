<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Feed;

use App\ApiResource\Feed\FeedInput;
use PHPUnit\Framework\TestCase;

class FeedInputTest extends TestCase
{
    public function testConstructor(): void
    {
        $input = new FeedInput();

        $this->assertInstanceOf(FeedInput::class, $input);
    }

    public function testReadonlyProperties(): void
    {
        $input = new FeedInput();

        $reflection = new \ReflectionClass($input);
        $this->assertTrue($reflection->isReadOnly());
    }
}
