<?php

declare(strict_types=1);

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait LikeableTrait
{
    public const LIKE_SERIALIZATION_GROUP_READ = 'like:read';

    #[Groups([self::LIKE_SERIALIZATION_GROUP_READ])]
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $likesCount = 0;

    #[Groups([self::LIKE_SERIALIZATION_GROUP_READ])]
    private bool $isLiked = false;

    public function getLikesCount(): int
    {
        return $this->likesCount;
    }

    public function setLikesCount(int $likesCount): void
    {
        $this->likesCount = $likesCount;
    }

    public function getIsLiked(): bool
    {
        return $this->isLiked;
    }

    public function setIsLiked(bool $isLiked): void
    {
        $this->isLiked = $isLiked;
    }
}
