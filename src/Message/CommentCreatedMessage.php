<?php

declare(strict_types=1);

namespace App\Message;

use App\Entity\Post;
use App\Entity\User;

readonly class CommentCreatedMessage
{
    public function __construct(
        public Post $post,
        public User $user,
    ) {
    }
}
