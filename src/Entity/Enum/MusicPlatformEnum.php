<?php

declare(strict_types=1);

namespace App\Entity\Enum;

enum MusicPlatformEnum: string
{
    case Spotify = 'spotify';
    case Deezer = 'deezer';
    case SoundCloud = 'soundcloud';

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
