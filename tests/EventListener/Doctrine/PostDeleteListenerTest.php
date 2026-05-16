<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Doctrine;

use App\Constants\RedisKeys;
use App\Entity\Enum\LikeableTypeEnum;
use App\Entity\Enum\ReportableTypeEnum;
use App\Entity\Like;
use App\Entity\Post;
use App\Entity\Report;
use App\EventListener\Doctrine\PostDeleteListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PostDeleteListenerTest extends TestCase
{
    /**
     * @var \Redis&MockObject
     */
    private \Redis $redis;

    private PostDeleteListener $listener;

    protected function setUp(): void
    {
        $this->redis = $this->createMock(\Redis::class);
        $this->listener = new PostDeleteListener($this->redis);
    }

    public function testPreRemoveDoesNothingWhenPostIdIsNull(): void
    {
        $post = $this->createMock(Post::class);
        $post->method('getId')->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('getRepository');
        $em->expects($this->never())->method('remove');
        $this->redis->expects($this->never())->method('del');
        $this->redis->expects($this->never())->method('keys');

        $event = new PreRemoveEventArgs($post, $em);

        $this->listener->preRemove($post, $event);
    }

    public function testPreRemoveRemovesLikesReportsAndDeletesAllRedisKeys(): void
    {
        $postId = 42;
        $post = $this->createMock(Post::class);
        $post->method('getId')->willReturn($postId);

        $like = $this->createMock(Like::class);
        $report = $this->createMock(Report::class);

        /** @var EntityRepository<Like>&MockObject $likeRepo */
        $likeRepo = $this->createMock(EntityRepository::class);
        $likeRepo->method('findBy')
            ->with(['entityId' => $postId, 'entityClass' => LikeableTypeEnum::Post])
            ->willReturn([$like])
        ;

        /** @var EntityRepository<Report>&MockObject $reportRepo */
        $reportRepo = $this->createMock(EntityRepository::class);
        $reportRepo->method('findBy')
            ->with(['entityId' => $postId, 'entityClass' => ReportableTypeEnum::Post])
            ->willReturn([$report])
        ;

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturnMap([
            [Like::class, $likeRepo],
            [Report::class, $reportRepo],
        ]);

        $removedEntities = [];
        $em->method('remove')->willReturnCallback(static function (object $entity) use (&$removedEntities): void {
            $removedEntities[] = $entity;
        });

        $debounceKey = RedisKeys::POST_VIEW_DEBOUNCE_PREFIX.$postId.':user:1';
        $this->redis->method('keys')
            ->with(RedisKeys::POST_VIEW_DEBOUNCE_PREFIX.$postId.':*')
            ->willReturn([$debounceKey])
        ;

        $delCalls = [];
        $this->redis->method('del')->willReturnCallback(static function () use (&$delCalls): int {
            $delCalls[] = \func_get_args();

            return 1;
        });

        $event = new PreRemoveEventArgs($post, $em);
        $this->listener->preRemove($post, $event);

        $this->assertSame([$like, $report], $removedEntities);
        $this->assertCount(2, $delCalls);
        $this->assertSame([RedisKeys::POST_VIEWS_PREFIX.$postId], $delCalls[0]);
        $this->assertSame([$debounceKey], $delCalls[1]);
    }

    public function testPreRemoveSkipsDebounceKeyDeletionWhenNoneExist(): void
    {
        $postId = 7;
        $post = $this->createMock(Post::class);
        $post->method('getId')->willReturn($postId);

        /** @var EntityRepository<Like>&MockObject $likeRepo */
        $likeRepo = $this->createMock(EntityRepository::class);
        $likeRepo->method('findBy')->willReturn([]);

        /** @var EntityRepository<Report>&MockObject $reportRepo */
        $reportRepo = $this->createMock(EntityRepository::class);
        $reportRepo->method('findBy')->willReturn([]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturnMap([
            [Like::class, $likeRepo],
            [Report::class, $reportRepo],
        ]);
        $em->expects($this->never())->method('remove');

        $this->redis->method('keys')
            ->with(RedisKeys::POST_VIEW_DEBOUNCE_PREFIX.$postId.':*')
            ->willReturn([])
        ;

        $delCount = 0;
        $this->redis->method('del')->willReturnCallback(static function () use (&$delCount): int {
            $delCount++;

            return 1;
        });

        $event = new PreRemoveEventArgs($post, $em);
        $this->listener->preRemove($post, $event);

        $this->assertSame(1, $delCount);
    }
}
