<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::preRemove, entity: Comment::class)]
class CommentEventListener
{
    public function preRemove(Comment $comment): void
    {
        $post = $comment->getPost();
        $post->decrementCommentsCount();
    }
}
