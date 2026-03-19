<?php

declare(strict_types=1);

namespace App\ApiResource\Mercure;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use App\State\Mercure\MercureTokenProvider;

/**
 * @codeCoverageIgnore
 */
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/mercure/token',
            shortName: 'Mercure',
            output: MercureTokenOutput::class,
            provider: MercureTokenProvider::class,
            openapi: new Operation(
                summary: 'Get Mercure JWT token',
                description: 'Retrieves a JWT token for subscribing to Mercure real-time updates on user conversations.',
                responses: [
                    '200' => new Response(description: 'Mercure token retrieved successfully'),
                    '401' => new Response(description: 'Authentication required'),
                ]
            )
        ),
    ],
)]
class MercureTokenOutput
{
    public string $token;

    /** @var array<string> */
    public array $topics = [];
}
