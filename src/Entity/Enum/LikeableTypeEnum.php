<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Entity\Message;
use App\Entity\Post;

enum LikeableTypeEnum: string
{
    case Post = 'post';
    //    case Comment = 'comment';
    case Message = 'message';

    public function toEntityClass(): string
    {
        return match ($this) {
            self::Post => Post::class,
            //            self::Comment => Comment::class,
            self::Message => Message::class,
        };
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
