<?php

declare(strict_types=1);

namespace App\Entity\Enum;

enum MercureTypeEnum: string
{
    case Message = 'message';

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
