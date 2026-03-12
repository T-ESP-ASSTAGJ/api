<?php

declare(strict_types=1);

namespace App\ApiResource\Report;

use Symfony\Component\Serializer\Annotation\Groups;

class ReportReasonOutput
{
    public const SERIALIZATION_GROUP_READ = 'report_reason:read';

    #[Groups([self::SERIALIZATION_GROUP_READ])]
    public string $key;

    #[Groups([self::SERIALIZATION_GROUP_READ])]
    public string $label;

    public function __construct(string $key, string $label)
    {
        $this->key = $key;
        $this->label = $label;
    }
}
