<?php

declare(strict_types=1);

namespace App\Service\Post;

use App\Constants\RedisKeys;
use App\Entity\Post;

class PostViewCounter
{
    private \Redis $redis;

    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    public function increment(Post $post, int $userId): void
    {
        $postId = $post->getId();
        $debounceKey = RedisKeys::POST_VIEW_DEBOUNCE_PREFIX.$postId.':'.$userId;
        $viewsKey = RedisKeys::POST_VIEWS_PREFIX.$postId;

        $isNewView = $this->redis->set($debounceKey, '1', ['nx', 'ex' => RedisKeys::DEBOUNCE_TTL]);

        if ($isNewView) {
            if (!$this->redis->exists($viewsKey)) {
                $this->redis->set($viewsKey, (string) $post->getViewsCount());
            }

            $this->redis->incr($viewsKey);
        }
    }

    public function getViews(Post $post): int
    {
        $postId = $post->getId();
        $viewsKey = RedisKeys::POST_VIEWS_PREFIX.$postId;

        $val = $this->redis->get($viewsKey);

        if (!$val) {
            $dbViews = $post->getViewsCount();
            $this->redis->set($viewsKey, (string) $dbViews);

            return $dbViews;
        }

        return (int) $val;
    }
}
