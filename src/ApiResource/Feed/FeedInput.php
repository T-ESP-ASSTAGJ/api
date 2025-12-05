<?php

declare(strict_types=1);

namespace App\ApiResource\Feed;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation;
use App\Entity\Post;
use App\State\Feed\FeedPrivateProvider;
use App\State\Feed\FeedPublicProvider;

#[ApiResource(
    shortName: 'Feed',
    operations: [
        new GetCollection(
            uriTemplate: '/feed/public',
            openapi: new Operation(
                summary: 'Get Public Feed',
                description: 'Returns the latest posts from all users'
            ),
            normalizationContext: ['groups' => [Post::SERIALIZATION_GROUP_READ]],
            provider: FeedPublicProvider::class
        ),
        new GetCollection(
            uriTemplate: '/feed/private',
            openapi: new Operation(
                summary: 'Get Private Feed',
                description: 'Returns posts from followed users only'
            ),
            normalizationContext: ['groups' => [Post::SERIALIZATION_GROUP_READ]],
            provider: FeedPrivateProvider::class
        ),
    ],
    paginationEnabled: true
)]
readonly class FeedInput
{
    public function __construct(
    ) {
    }
}
