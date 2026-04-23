<?php

declare(strict_types=1);

namespace App\State\Search;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Search\SearchTypeEnum;
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
        $type = SearchTypeEnum::from($context['filters']['type'] ?? SearchTypeEnum::Users->value);

        $offset = $this->pagination->getOffset($operation, $context);
        $limit = $this->pagination->getLimit($operation, $context);

        return $this->resolveResults($type, $sanitizedQuery, $offset, $limit);
    }

    /**
     * @return array<object>
     */
    private function resolveResults(SearchTypeEnum $type, string $query, int $offset, int $limit): array
    {
        if (SearchTypeEnum::Users === $type) {
            return $this->userRepository->searchByQuery($query, $offset, $limit);
        }

        if (SearchTypeEnum::Tracks === $type) {
            $posts = $this->postRepository->searchByTrackTitle($query, $offset, $limit);
            $this->isLikedEnricher->enrich($posts, Post::class);

            return $posts;
        }

        if (SearchTypeEnum::Artists === $type) {
            $posts = $this->postRepository->searchByArtistName($query, $offset, $limit);
            $this->isLikedEnricher->enrich($posts, Post::class);

            return $posts;
        }

        $posts = $this->postRepository->searchByQuery($query, $offset, $limit);
        $this->isLikedEnricher->enrich($posts, Post::class);

        return $posts;
    }
}
