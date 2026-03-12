<?php

declare(strict_types=1);

namespace App\Entity\Enum;

enum ReportReasonEnum: string
{
    case Spam = 'spam';
    case Harassment = 'harassment';
    case HatefulContent = 'hateful_content';
    case Offensive = 'offensive';
    case Other = 'other';

    public function getLabel(): string
    {
        return match($this) {
            self::Spam => 'Spam',
            self::Harassment => 'Harcèlement',
            self::HatefulContent => 'Contenu haineux',
            self::Offensive => 'Injurieux',
            self::Other => 'Autre',
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
