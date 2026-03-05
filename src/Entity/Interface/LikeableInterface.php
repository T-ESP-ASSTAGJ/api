<?php

declare(strict_types=1);

namespace App\Entity\Interface;

use App\Entity\User;

interface LikeableInterface
{
    public function getId(): ?int;
    public function getUser(): User;

    public function getLikesCount(): int;

    public function setLikesCount(int $likesCount): void;

    public function getIsLiked(): bool;

    public function setIsLiked(bool $isLiked): void;
}
