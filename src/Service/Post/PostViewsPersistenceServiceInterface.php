<?php

declare(strict_types=1);

namespace App\Service\Post;

interface PostViewsPersistenceServiceInterface {
    public function persistViews(int $postId): void;
}