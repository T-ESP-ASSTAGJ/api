<?php

declare(strict_types=1);

namespace App\Entity\Enum;

enum MessageTypeEnum: string
{
    case Text = 'text';
    case Image = 'image';
    case Music = 'music';
    case Share = 'share';

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
