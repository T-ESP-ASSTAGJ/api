<?php

declare(strict_types=1);

namespace App\Tests\Entity\Enum;

use App\Entity\Comment;
use App\Entity\Enum\LikeableTypeEnum;
use App\Entity\Message;
use App\Entity\Post;
use PHPUnit\Framework\TestCase;

class LikeableTypeEnumTest extends TestCase
{
    public function testEnumCases(): void
    {
        $this->assertSame(Post::class, LikeableTypeEnum::Post->value);
        $this->assertSame(Comment::class, LikeableTypeEnum::Comment->value);
        $this->assertSame(Message::class, LikeableTypeEnum::Message->value);
    }

    public function testValues(): void
    {
        $values = LikeableTypeEnum::values();

        $this->assertCount(3, $values);
        $this->assertContains(Post::class, $values);
        $this->assertContains(Comment::class, $values);
        $this->assertContains(Message::class, $values);
    }
}
