<?php

declare(strict_types=1);

namespace App\Tests\Service\Post;

use App\Constants\RedisKeys;
use App\Repository\PostRepository;
use App\Service\Post\PostViewsPersistenceService;
use PHPUnit\Framework\TestCase;

class PostViewsPersistenceServiceTest extends TestCase
{
    public function testPersistViewsUpdatesRepositoryWhenRedisHasViews(): void
    {
        $redis = $this->createMock(\Redis::class);
        $redis->method('get')
            ->with(RedisKeys::POST_VIEWS_PREFIX.'1')
            ->willReturn('42')
        ;

        $repo = $this->createMock(PostRepository::class);
        $repo->expects($this->once())->method('updateViewsCount')->with(1, 42);

        (new PostViewsPersistenceService($repo, $redis))->persistViews(1);
    }

    public function testPersistViewsSkipsUpdateWhenRedisHasZeroViews(): void
    {
        $redis = $this->createMock(\Redis::class);
        $redis->method('get')->willReturn(false);

        $repo = $this->createMock(PostRepository::class);
        $repo->expects($this->never())->method('updateViewsCount');

        (new PostViewsPersistenceService($repo, $redis))->persistViews(2);
    }
}
