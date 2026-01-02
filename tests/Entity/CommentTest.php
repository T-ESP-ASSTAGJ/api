<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class CommentTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $comment = new Comment();
        $post = new Post();
        $user = new User();

        $this->assertNull($comment->getId());

        $result = $comment->setPost($post);
        $this->assertSame($comment, $result);
        $this->assertSame($post, $comment->getPost());

        $result = $comment->setUser($user);
        $this->assertSame($comment, $result);
        $this->assertSame($user, $comment->getUser());

        $result = $comment->setContent('This is a comment');
        $this->assertSame($comment, $result);
        $this->assertSame('This is a comment', $comment->getContent());
    }

    public function testSetPostAddsCommentToPostCollection(): void
    {
        $comment = new Comment();
        $post = new Post();

        $this->assertCount(0, $post->getComments());

        $comment->setPost($post);

        $this->assertCount(1, $post->getComments());
        $this->assertTrue($post->getComments()->contains($comment));
    }

    public function testSetPostDoesNotDuplicateInCollection(): void
    {
        $comment = new Comment();
        $post = new Post();

        $comment->setPost($post);
        $comment->setPost($post); // Set again

        $this->assertCount(1, $post->getComments());
    }

    public function testTimeStampableTrait(): void
    {
        $comment = new Comment();
        $comment->setCreatedAt();

        $this->assertInstanceOf(\DateTimeImmutable::class, $comment->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $comment->getUpdatedAt());
    }

    public function testLikeableTrait(): void
    {
        $comment = new Comment();

        $this->assertSame(0, $comment->getLikesCount());
        $this->assertFalse($comment->getIsLiked());

        $comment->setLikesCount(5);
        $this->assertSame(5, $comment->getLikesCount());

        $comment->setIsLiked(true);
        $this->assertTrue($comment->getIsLiked());
    }
}
