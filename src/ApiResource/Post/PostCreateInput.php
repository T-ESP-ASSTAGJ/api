<?php

declare(strict_types=1);

namespace App\ApiResource\Post;

use Symfony\Component\Validator\Constraints as Assert;

class PostCreateInput
{
    #[Assert\NotBlank]
    public int $userId;

    #[Assert\Url]
    #[Assert\Length(max: 500)]
    public ?string $songPreviewUrl = null;

    #[Assert\Length(max: 1000)]
    public ?string $caption = null;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $trackId;

    #[Assert\Url]
    #[Assert\Length(max: 500)]
    public ?string $photoUrl = null;

    #[Assert\Length(max: 255)]
    public ?string $location = null;
}
