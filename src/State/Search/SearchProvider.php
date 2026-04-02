<?php

declare(strict_types=1);

namespace App\State\Search;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Post;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\isLikedEnricher;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @implements ProviderInterface<Post|object>
 */
final readonly class SearchProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.pagination')]
        private Pagination $pagination,
        private PostRepository $postRepository,
        private UserRepository $userRepository,
        private isLikedEnricher $isLikedEnricher,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return array<object>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $query = trim($context['filters']['query'] ?? '');

        if ('' === $query) {
            throw new BadRequestHttpException('Le paramètre "query" est obligatoire et ne peut pas être vide.');
        }

        $sanitizedQuery = strip_tags($query);
        $type = $context['filters']['type'] ?? 'posts';

        $offset = $this->pagination->getOffset($operation, $context);
        $limit = $this->pagination->getLimit($operation, $context);

        if ('users' === $type) {
            return $this->userRepository->searchByQuery($sanitizedQuery, $offset, $limit);
        }

        $posts = $this->postRepository->searchByQuery($sanitizedQuery, $offset, $limit);
        $this->isLikedEnricher->enrich($posts, Post::class);

        return $posts;
    }
}
