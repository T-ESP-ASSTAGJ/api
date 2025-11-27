<?php

declare(strict_types=1);

namespace App\ApiResource\Feed;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation;
use App\ApiResource\Post\PostGetOutput;
use App\State\Feed\FeedPrivateProvider;
use App\State\Feed\FeedPublicProvider;

#[ApiResource(
    paginationEnabled: true,
    shortName: 'Feed',
    operations: [
        new GetCollection(
            paginationEnabled: true,
            uriTemplate: '/feed/public',
            output: PostGetOutput::class,
            provider: FeedPublicProvider::class,
            openapi: new Operation(
                summary: 'Get Public Feed',
                description: 'Returns the latest posts from all users'
            )
        ),
        new GetCollection(
            paginationEnabled: true,
            uriTemplate: '/feed/private',
            output: PostGetOutput::class,
            provider: FeedPrivateProvider::class,
            openapi: new Operation(
                summary: 'Get Private Feed',
                description: 'Returns posts from followed users only'
            )
        ),
    ]
)]
readonly class FeedInput
{
    public function __construct(
    ) {
    }
}
