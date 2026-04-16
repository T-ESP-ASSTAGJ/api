<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Comment;
use App\Entity\Enum\LikeableTypeEnum;
use App\Entity\Like;
use App\Entity\Message;
use App\Entity\Post;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class LikeTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $like = new Like();
        $user = new User();

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

        $this->assertSame('post', $like->getEntityClassLabel());
    }

    public function testSetEntityClassWithComment(): void
    {
        $like = new Like();
        $like->setEntityClass(LikeableTypeEnum::Comment);

        $this->assertSame(Comment::class, $like->getEntityClass());
    }

    public function testSetEntityClassWithMessage(): void
    {
        $like = new Like();
        $like->setEntityClass(LikeableTypeEnum::Message);

        $this->assertSame(Message::class, $like->getEntityClass());
    }
}
