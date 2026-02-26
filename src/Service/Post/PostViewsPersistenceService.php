<?php

declare(strict_types=1);

namespace App\Service\Post;

use App\Constants\RedisKeys;
use App\Repository\PostRepository;

final readonly class PostViewsPersistenceService implements PostViewsPersistenceServiceInterface
{
    public function __construct(
        private PostRepository $postRepository,
        private \Redis $redis,
    ) {}

    public function persistViews(int $postId): void
    {
        $viewsKey = RedisKeys::POST_VIEWS_PREFIX . $postId;
        $redisViews = (int) ($this->redis->get($viewsKey) ?: 0);

        if ($redisViews > 0) {
            $this->postRepository->updateViewCount($postId, $redisViews);
        }
    }
}