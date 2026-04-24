<?php

declare(strict_types=1);

namespace App\EventListener\Doctrine;

use App\Constants\RedisKeys;
use App\Entity\Enum\LikeableTypeEnum;
use App\Entity\Enum\ReportableTypeEnum;
use App\Entity\Like;
use App\Entity\Post;
use App\Entity\Report;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::preRemove, method: 'preRemove', entity: Post::class)]
final readonly class PostDeleteListener
{
    public function __construct(
        private \Redis $redis,
    ) {
    }

    public function preRemove(Post $post, PreRemoveEventArgs $event): void
    {
        $postId = $post->getId();
        if (null === $postId) {
            return;
        }

        $entityManager = $event->getObjectManager();

        // 1. Remove associated Likes (polymorphic)
        $likes = $entityManager->getRepository(Like::class)->findBy([
            'entityId' => $postId,
            'entityClass' => LikeableTypeEnum::Post,
        ]);

        foreach ($likes as $like) {
            $entityManager->remove($like);
        }

        // 2. Remove associated Reports (polymorphic)
        $reports = $entityManager->getRepository(Report::class)->findBy([
            'entityId' => $postId,
            'entityClass' => ReportableTypeEnum::Post,
        ]);

        foreach ($reports as $report) {
            $entityManager->remove($report);
        }

        // 3. Remove Redis views count key
        $viewsKey = RedisKeys::POST_VIEWS_PREFIX.$postId;
        $this->redis->del($viewsKey);

        // 4. Remove all Redis debounce keys for this post
        $debouncePattern = RedisKeys::POST_VIEW_DEBOUNCE_PREFIX.$postId.':*';
        $keys = $this->redis->keys($debouncePattern);
        if (!empty($keys)) {
            $this->redis->del(...$keys);
        }
    }
}
