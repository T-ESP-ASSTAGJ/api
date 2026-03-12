<?php

declare(strict_types=1);

namespace App\ApiResource\Report;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\State\Report\ReportReasonsProvider;

#[ApiResource(
    shortName: 'ReportReason',
    operations: [
        new GetCollection(
            uriTemplate: '/report-reasons',
            provider: ReportReasonsProvider::class,
        ),
    ]
)]
class ReportReasonOutput
{
    public string $key;
    public string $label;

    public function __construct(string $key, string $label)
    {
        $this->key = $key;
        $this->label = $label;
    }
}
