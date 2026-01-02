<?php

declare(strict_types=1);

namespace App\Tests\Entity\Trait;

use App\Entity\Post;
use PHPUnit\Framework\TestCase;

class LikeableTraitTest extends TestCase
{
    public function testGetLikesCountDefaultValue(): void
    {
        $entity = new Post();

        $this->assertSame(0, $entity->getLikesCount());
    }

    public function testSetAndGetLikesCount(): void
    {
        $entity = new Post();

        $entity->setLikesCount(42);

        $this->assertSame(42, $entity->getLikesCount());
    }

    public function testGetIsLikedDefaultValue(): void
    {
        $entity = new Post();

        $this->assertFalse($entity->getIsLiked());
    }

    public function testSetAndGetIsLiked(): void
    {
        $entity = new Post();

        $entity->setIsLiked(true);

        $this->assertTrue($entity->getIsLiked());

        $entity->setIsLiked(false);

        $this->assertFalse($entity->getIsLiked());
    }

    public function testLikesCountCanBeNegative(): void
    {
        $entity = new Post();

        $entity->setLikesCount(-5);

        $this->assertSame(-5, $entity->getLikesCount());
    }

    public function testLikesCountCanBeZero(): void
    {
        $entity = new Post();

        $entity->setLikesCount(10);
        $entity->setLikesCount(0);

        $this->assertSame(0, $entity->getLikesCount());
    }
}
