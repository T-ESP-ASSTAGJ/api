<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Like;

use App\ApiResource\Like\LikeCreateInput;
use App\Entity\Enum\LikeableTypeEnum;
use PHPUnit\Framework\TestCase;

class LikeCreateInputTest extends TestCase
{
    public function testPublicProperties(): void
    {
        $input = new LikeCreateInput();
        $input->entityClass = LikeableTypeEnum::Post;
        $input->entityId = 42;

        $this->assertSame(LikeableTypeEnum::Post, $input->entityClass);
        $this->assertSame(42, $input->entityId);
    }

    public function testCanSetEntityClassToComment(): void
    {
        $input = new LikeCreateInput();
        $input->entityClass = LikeableTypeEnum::Comment;

        $this->assertSame(LikeableTypeEnum::Comment, $input->entityClass);
    }

    public function testCanSetEntityClassToMessage(): void
    {
        $input = new LikeCreateInput();
        $input->entityClass = LikeableTypeEnum::Message;

        $this->assertSame(LikeableTypeEnum::Message, $input->entityClass);
    }
}
