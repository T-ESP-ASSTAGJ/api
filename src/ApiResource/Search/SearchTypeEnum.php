<?php

declare(strict_types=1);

namespace App\ApiResource\Search;

enum SearchTypeEnum: string
{
    case Users = 'users';
    case Posts = 'posts';
    case Tracks = 'tracks';
    case Artists = 'artists';
}
