<?php

declare(strict_types=1);

namespace App\ApiResource\Search;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use App\Entity\Post;
use App\Entity\User;
use App\State\Search\SearchProvider;

#[ApiResource(
    shortName: 'Search',
    operations: [
        new GetCollection(
            uriTemplate: '/search',
            normalizationContext: ['groups' => [
                Post::SERIALIZATION_GROUP_READ,
                Post::LIKE_SERIALIZATION_GROUP_READ,
                User::SERIALIZATION_GROUP_READ,
            ]],
            provider: SearchProvider::class,
            parameters: [
                'query' => new QueryParameter(required: true, description: 'Terme de recherche'),
                'type' => new QueryParameter(required: false, description: 'Type de contenu : users (défaut), posts, tracks ou artists'),
            ],
        ),
    ]
)]
class Search
{
}
