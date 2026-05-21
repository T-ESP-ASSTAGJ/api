<?php

declare(strict_types=1);

namespace App\Service\Post;

use App\Constants\RedisKeys;
use App\Repository\PostRepository;

/**
 * Persiste le compteur de vues Redis d'une publication en base de données.
 *
 * Appelé de manière asynchrone par un gestionnaire de messages après l'incrémentation d'une vue.
 * Ignore l'écriture en BDD si Redis contient zéro, ce qui évite des requêtes inutiles lors de défauts de cache.
 */
final readonly class PostViewsPersistenceService implements PostViewsPersistenceServiceInterface
{
    public function __construct(
        private PostRepository $postRepository,
        private \Redis $redis,
    ) {
    }

    public function persistViews(int $postId): void
    {
        $viewsKey = RedisKeys::POST_VIEWS_PREFIX.$postId;
        $redisViews = (int) ($this->redis->get($viewsKey) ?: 0);

        if ($redisViews > 0) {
            $this->postRepository->updateViewsCount($postId, $redisViews);
        }
    }
}
