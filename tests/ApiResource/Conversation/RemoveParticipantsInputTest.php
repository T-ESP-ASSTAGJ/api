<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Conversation;

use App\ApiResource\Conversation\RemoveParticipantsInput;
use PHPUnit\Framework\TestCase;

class RemoveParticipantsInputTest extends TestCase
{
    public function testConstructorWithDefaultValues(): void
    {
        $input = new RemoveParticipantsInput();

        $this->assertSame([], $input->userIds);
    }

    public function testConstructorWithUserIds(): void
    {
        $userIds = [1, 2, 3];
        $input = new RemoveParticipantsInput(userIds: $userIds);

        $this->assertSame($userIds, $input->userIds);
    }

    public function testReadonlyProperties(): void
    {
        $input = new RemoveParticipantsInput(userIds: [1, 2]);

        $reflection = new \ReflectionClass($input);
        $this->assertTrue($reflection->isReadOnly());
    }
}
