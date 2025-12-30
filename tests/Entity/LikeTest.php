<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Enum\LikeableTypeEnum;
use App\Entity\Like;
use App\Entity\Post;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class LikeTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $like = new Like();
        $user = new User();
        $post = new Post();

        $this->assertNull($like->getId());

        $result = $like->setUser($user);
        $this->assertSame($like, $result);
        $this->assertSame($user, $like->getUser());

        $result = $like->setEntityId(42);
        $this->assertSame($like, $result);
        $this->assertSame(42, $like->getEntityId());

        $result = $like->setEntityClass(LikeableTypeEnum::Post);
        $this->assertSame($like, $result);
        $this->assertSame(Post::class, $like->getEntityClass());

        $like->setLikedEntity($post);
        $this->assertSame($post, $like->getLikedEntity());
    }

    public function testTimeStampableTrait(): void
    {
        $like = new Like();
        $like->setCreatedAt();

        $this->assertInstanceOf(\DateTimeImmutable::class, $like->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $like->getUpdatedAt());
    }
}
