<?php

declare(strict_types=1);

namespace App\Tests\Entity\Enum;

use App\Entity\Comment;
use App\Entity\Enum\ReportableTypeEnum;
use App\Entity\Post;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ReportableTypeEnumTest extends TestCase
{
    public function testEnumCases(): void
    {
        $this->assertSame(Post::class, ReportableTypeEnum::Post->value);
        $this->assertSame(Comment::class, ReportableTypeEnum::Comment->value);
        $this->assertSame(User::class, ReportableTypeEnum::User->value);
    }

    public function testToEntityClass(): void
    {
        $this->assertSame(Post::class, ReportableTypeEnum::Post->toEntityClass());
        $this->assertSame(Comment::class, ReportableTypeEnum::Comment->toEntityClass());
        $this->assertSame(User::class, ReportableTypeEnum::User->toEntityClass());
    }

    public function testValues(): void
    {
        $values = ReportableTypeEnum::values();

        $this->assertIsArray($values);
        $this->assertCount(3, $values);
        $this->assertContains(Post::class, $values);
        $this->assertContains(Comment::class, $values);
        $this->assertContains(User::class, $values);
    }
}
