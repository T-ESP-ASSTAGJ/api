<?php

namespace App\Entity\Enum;

enum LikeableTypeEnum: string
{
    case Post = 'post';
    case Comment = 'comment';
    case Image = 'message';
}
