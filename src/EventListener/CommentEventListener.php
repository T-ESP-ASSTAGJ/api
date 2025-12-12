<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postPersist, entity: Comment::class)]
#[AsEntityListener(event: Events::preRemove, entity: Comment::class)]
class CommentEventListener
{
    public function postPersist(Comment $comment): void
    {
        $post = $comment->getPost();
        $post->incrementCommentsCount();
    }

    public function preRemove(Comment $comment): void
    {
        $post = $comment->getPost();
        $post->decrementCommentsCount();
    }
}