<?php

declare(strict_types=1);

namespace App\ApiResource\Mercure;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use App\State\Mercure\MercureTokenProvider;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/mercure/token',
            openapi: new Operation(
                responses: [
                    '200' => new Response(description: 'Mercure token retrieved successfully'),
                    '401' => new Response(description: 'Authentication required'),
                ],
                summary: 'Get Mercure JWT token',
                description: 'Retrieves a JWT token for subscribing to Mercure real-time updates on user conversations.',
            ),
            shortName: 'Mercure',
            output: MercureTokenOutput::class,
            provider: MercureTokenProvider::class,
        ),
    ],
)]
class MercureTokenOutput
{
    public string $token;

    /**
     * @var array<string>
     */
    public array $topics = [];
}
