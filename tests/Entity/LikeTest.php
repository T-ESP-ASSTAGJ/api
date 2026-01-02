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

    public function testSetEntityClassWithComment(): void
    {
        $like = new Like();
        $like->setEntityClass(LikeableTypeEnum::Comment);

        $this->assertSame(\App\Entity\Comment::class, $like->getEntityClass());
    }

    public function testSetEntityClassWithMessage(): void
    {
        $like = new Like();
        $like->setEntityClass(LikeableTypeEnum::Message);

        $this->assertSame(\App\Entity\Message::class, $like->getEntityClass());
    }

    public function testGetLikedEntityReturnsNullByDefault(): void
    {
        $like = new Like();

        $this->assertNull($like->getLikedEntity());
    }

    public function testSetLikedEntity(): void
    {
        $like = new Like();
        $post = new Post();

        $like->setLikedEntity($post);

        $this->assertSame($post, $like->getLikedEntity());

        $like->setLikedEntity(null);
        $this->assertNull($like->getLikedEntity());
    }
}
