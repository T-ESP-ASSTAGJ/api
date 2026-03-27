<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;

enum ReportableTypeEnum: string
{
    case Post = Post::class;
    case Comment = Comment::class;
    case User = User::class;

    public function toEntityClass(): string
    {
        return $this->value;
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
