<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Conversation;

use App\ApiResource\Conversation\ConversationCreateInput;
use PHPUnit\Framework\TestCase;

class ConversationCreateInputTest extends TestCase
{
    public function testConstructorWithDefaultValues(): void
    {
        $input = new ConversationCreateInput();

        $this->assertFalse($input->isGroup);
        $this->assertNull($input->groupName);
        $this->assertNull($input->participants);
    }

    public function testConstructorWithGroupConversation(): void
    {
        $input = new ConversationCreateInput(
            isGroup: true,
            groupName: 'Test Group',
            participants: [1, 2, 3]
        );

        $this->assertTrue($input->isGroup);
        $this->assertSame('Test Group', $input->groupName);
        $this->assertSame([1, 2, 3], $input->participants);
    }

    public function testConstructorWithPrivateConversation(): void
    {
        $input = new ConversationCreateInput(
            isGroup: false,
            groupName: null,
            participants: [1]
        );

        $this->assertFalse($input->isGroup);
        $this->assertNull($input->groupName);
        $this->assertSame([1], $input->participants);
    }

    public function testReadonlyProperties(): void
    {
        $input = new ConversationCreateInput();

        $reflection = new \ReflectionClass($input);
        $this->assertTrue($reflection->isReadOnly());
    }
}
