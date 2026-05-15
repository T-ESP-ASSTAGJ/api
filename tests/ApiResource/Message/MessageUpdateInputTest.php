<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Message;

use App\ApiResource\Message\MessageUpdateInput;
use PHPUnit\Framework\TestCase;

class MessageUpdateInputTest extends TestCase
{
    public function testDefaultContentIsNull(): void
    {
        $input = new MessageUpdateInput();

        $this->assertNull($input->content);
    }

    public function testCanSetContent(): void
    {
        $input = new MessageUpdateInput();
        $input->content = 'Updated message content';

        $this->assertSame('Updated message content', $input->content);
    }

    public function testCanSetContentToNull(): void
    {
        $input = new MessageUpdateInput();
        $input->content = 'some content';
        $input->content = null;

        $this->assertNull($input->content);
    }
}
