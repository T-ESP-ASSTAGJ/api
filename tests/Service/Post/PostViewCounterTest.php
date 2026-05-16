<?php

declare(strict_types=1);

namespace App\Tests\Service\Post;

use App\Constants\RedisKeys;
use App\Entity\Post;
use App\Service\Post\PostViewCounter;
use PHPUnit\Framework\TestCase;

class PostViewCounterTest extends TestCase
{
    /**
     * @var \Redis&\PHPUnit\Framework\MockObject\MockObject
     */
    private \Redis $redis;

    private PostViewCounter $service;

    protected function setUp(): void
    {
        $this->redis = $this->createMock(\Redis::class);
        $this->service = new PostViewCounter($this->redis);
    }

    public function testIncrementSetsDebounceAndInitializesViewsKeyWhenNew(): void
    {
        $post = $this->createMock(Post::class);
        $post->method('getId')->willReturn(42);
        $post->method('getViewsCount')->willReturn(10);

        $debounceKey = RedisKeys::POST_VIEW_DEBOUNCE_PREFIX.'42:7';
        $viewsKey = RedisKeys::POST_VIEWS_PREFIX.'42';

        $setCalls = [];
        $this->redis->expects($this->exactly(2))
            ->method('set')
            ->willReturnCallback(static function () use (&$setCalls): bool {
                $setCalls[] = \func_get_args();

                return true;
            })
        ;
        $this->redis->expects($this->once())
            ->method('exists')
            ->with($viewsKey)
            ->willReturn(0)
        ;
        $this->redis->expects($this->once())
            ->method('incr')
            ->with($viewsKey)
        ;

        $this->service->increment($post, 7);

        $this->assertSame([$debounceKey, '1', ['nx', 'ex' => RedisKeys::DEBOUNCE_TTL]], $setCalls[0]);
        $this->assertSame($viewsKey, $setCalls[1][0]);
        $this->assertSame('10', $setCalls[1][1]);
    }

    public function testIncrementSkipsInitWhenViewsKeyAlreadyExists(): void
    {
        $post = $this->createMock(Post::class);
        $post->method('getId')->willReturn(5);
        $post->method('getViewsCount')->willReturn(3);

        $this->redis->method('set')->willReturn(true);
        $this->redis->expects($this->once())->method('exists')->willReturn(1);
        $this->redis->expects($this->never())->method('setex');
        $this->redis->expects($this->once())->method('incr');

        $this->service->increment($post, 1);
    }

    public function testIncrementDoesNothingWhenViewAlreadyDebounced(): void
    {
        $post = $this->createMock(Post::class);
        $post->method('getId')->willReturn(9);

        $this->redis->method('set')->willReturn(false);
        $this->redis->expects($this->never())->method('exists');
        $this->redis->expects($this->never())->method('incr');

        $this->service->increment($post, 2);
    }

    public function testGetViewsReturnsRedisValueWhenSet(): void
    {
        $post = $this->createMock(Post::class);
        $post->method('getId')->willReturn(3);

        $this->redis->method('get')
            ->with(RedisKeys::POST_VIEWS_PREFIX.'3')
            ->willReturn('99')
        ;

        $this->assertSame(99, $this->service->getViews($post));
    }

    public function testGetViewsFallsBackToDbAndSetsRedis(): void
    {
        $post = $this->createMock(Post::class);
        $post->method('getId')->willReturn(8);
        $post->method('getViewsCount')->willReturn(55);

        $viewsKey = RedisKeys::POST_VIEWS_PREFIX.'8';
        $this->redis->method('get')->with($viewsKey)->willReturn(false);
        $this->redis->expects($this->once())->method('set')->with($viewsKey, '55');

        $this->assertSame(55, $this->service->getViews($post));
    }
}
