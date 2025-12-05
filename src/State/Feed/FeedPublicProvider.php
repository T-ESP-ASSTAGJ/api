<?php

declare(strict_types=1);

namespace App\State\Feed;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Post;
use App\Repository\PostRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ProviderInterface<Post>
 */
final readonly class FeedPublicProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.pagination')]
        private Pagination $pagination,
        private PostRepository $postRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return array<Post>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $offset = $this->pagination->getOffset($operation, $context);
        $limit = $this->pagination->getLimit($operation, $context);

        return $this->postRepository->getPaginatedPosts($offset, $limit);
    }
}
