<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Post;
use App\Entity\Track;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class PostTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $post = new Post();
        $user = new User();
        $track = new Track();

        $this->assertNull($post->getId());

        $result = $post->setUser($user);
        $this->assertSame($post, $result);
        $this->assertSame($user, $post->getUser());

        $result = $post->setCaption('This is a test caption');
        $this->assertSame($post, $result);
        $this->assertSame('This is a test caption', $post->getCaption());

        $result = $post->setTrack($track);
        $this->assertSame($post, $result);
        $this->assertSame($track, $post->getTrack());

        $result = $post->setPhotoUrl('https://example.com/photo.jpg');
        $this->assertSame($post, $result);
        $this->assertSame('https://example.com/photo.jpg', $post->getPhotoUrl());

        $result = $post->setLocation('Paris, France');
        $this->assertSame($post, $result);
        $this->assertSame('Paris, France', $post->getLocation());

        $result = $post->setCommentsCount(5);
        $this->assertSame($post, $result);
        $this->assertSame(5, $post->getCommentsCount());
    }

    public function testCommentsCollection(): void
    {
        $post = new Post();

        $this->assertCount(0, $post->getComments());

        // Test that comments collection is initialized
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $post->getComments());
    }

    public function testDefaultValues(): void
    {
        $post = new Post();

        $this->assertNull($post->getCaption());
        $this->assertNull($post->getPhotoUrl());
        $this->assertNull($post->getLocation());
        $this->assertSame(0, $post->getCommentsCount());
    }

    public function testTimeStampableTrait(): void
    {
        $post = new Post();
        $post->setCreatedAt();

        $this->assertInstanceOf(\DateTimeImmutable::class, $post->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $post->getUpdatedAt());
    }

    public function testLikeableTrait(): void
    {
        $post = new Post();

        $this->assertSame(0, $post->getLikesCount());
        $this->assertFalse($post->getIsLiked());

        $post->setLikesCount(10);
        $this->assertSame(10, $post->getLikesCount());

        $post->setIsLiked(true);
        $this->assertTrue($post->getIsLiked());
    }
}
