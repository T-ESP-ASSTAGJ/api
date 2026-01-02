<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Conversation;

use App\ApiResource\Conversation\AddParticipantsInput;
use PHPUnit\Framework\TestCase;

class AddParticipantsInputTest extends TestCase
{
    public function testConstructorWithDefaultValues(): void
    {
        $input = new AddParticipantsInput();

        $this->assertSame([], $input->userIds);
    }

    public function testConstructorWithUserIds(): void
    {
        $userIds = [1, 2, 3];
        $input = new AddParticipantsInput(userIds: $userIds);

        $this->assertSame($userIds, $input->userIds);
    }

    public function testReadonlyProperties(): void
    {
        $input = new AddParticipantsInput(userIds: [1, 2]);

        $reflection = new \ReflectionClass($input);
        $this->assertTrue($reflection->isReadOnly());
    }
}
