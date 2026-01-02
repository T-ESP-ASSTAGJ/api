<?php

declare(strict_types=1);

namespace App\ApiResource\Post;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @codeCoverageIgnore
 */
class PostCreateInput
{
    #[Assert\Length(max: 1000)]
    #[ApiProperty(example: 'Amazing sunset vibes! 🌅 #music #vibes')]
    public ?string $caption = null;

    #[Assert\NotBlank]
    #[Assert\Positive]
    #[ApiProperty(example: 1)]
    public int $trackId;

    #[Assert\Url]
    #[Assert\Length(max: 500)]
    #[ApiProperty(example: 'https://example.com/photos/sunset-beach.jpg')]
    public ?string $photoUrl = null;

    #[Assert\Length(max: 255)]
    #[ApiProperty(example: 'Paris, France')]
    public ?string $location = null;
}
