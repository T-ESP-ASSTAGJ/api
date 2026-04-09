<?php

declare(strict_types=1);

namespace App\ApiResource\Post;

use ApiPlatform\Metadata\ApiProperty;
use App\ApiResource\Track\TrackInput;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @codeCoverageIgnore
 */
class PostCreateInput
{
    public function __construct(
        public TrackInput $track,
        #[Assert\Length(max: 1000)]
        #[ApiProperty(example: 'Amazing sunset vibes! 🌅 #music #vibes')]
        public ?string $caption = null,
        #[Assert\Length(max: 7000000)]
        #[ApiProperty(example: 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD...')]
        public ?string $frontImage = null,
        #[Assert\Length(max: 7000000)]
        #[ApiProperty(example: 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD...')]
        public ?string $backImage = null,
        #[Assert\Length(max: 255)]
        #[ApiProperty(example: 'Paris, France')]
        public ?string $location = null,
    ) {
    }
}
