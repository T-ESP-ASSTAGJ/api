<?php

declare(strict_types=1);

namespace App\Entity\Enum;

enum VisibilityEnum: string
{
    case Public  = 'public';
    case Friends = 'friends';
    case Private = 'private';
}
