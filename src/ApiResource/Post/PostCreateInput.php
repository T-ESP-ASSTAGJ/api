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
    #[Assert\Length(max: 255)]
    #[ApiProperty(example: '1234567890')]
    public string $songId;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[ApiProperty(example: 'Blinding Lights')]
    public string $trackTitle;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[ApiProperty(example: 'The Weeknd')]
    public string $artistName;

    #[Assert\Positive]
    #[ApiProperty(example: 2020)]
    public ?int $releaseYear = null;

    #[Assert\Length(max: 1000000)]
    #[ApiProperty(example: 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD...')]
    public ?string $frontImage = null;

    #[Assert\Length(max: 1000000)]
    #[ApiProperty(example: 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD...')]
    public ?string $backImage = null;

    #[Assert\Length(max: 255)]
    #[ApiProperty(example: 'Paris, France')]
    public ?string $location = null;
}
