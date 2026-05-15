<?php

declare(strict_types=1);

namespace App\ApiResource\Post;

use ApiPlatform\Metadata\ApiProperty;
use App\ApiResource\Track\TrackInput;
use App\Validator\Constraints\Base64;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostCreateInput
{
    public function __construct(
        public TrackInput $track,
        #[Assert\Length(max: 1000)]
        #[ApiProperty(example: 'Amazing sunset vibes! 🌅 #music #vibes')]
        public ?string $caption = null,
        #[Base64]
        #[ApiProperty(example: 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD...')]
        public ?string $frontImage = null,
        #[Base64]
        #[ApiProperty(example: 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD...')]
        public ?string $backImage = null,
        #[Assert\Length(max: 255)]
        #[ApiProperty(example: 'Paris, France')]
        public ?string $location = null,
    ) {
    }
}
