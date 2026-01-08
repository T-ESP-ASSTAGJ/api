<?php

declare(strict_types=1);

namespace App\ApiResource\Track;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @codeCoverageIgnore
 */
class TrackCreateInput
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[ApiProperty(example: '1234567890')]
    public string $songId;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[ApiProperty(example: 'Blinding Lights')]
    public string $title;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[ApiProperty(example: 'The Weeknd')]
    public string $artistName;

    #[Assert\Positive]
    #[ApiProperty(example: 2020)]
    public ?int $releaseYear = null;

    #[Assert\Length(max: 300)]
    #[ApiProperty(example: 'https://i.scdn.co/image/ab67616d0000b273123456789abcdef')]
    public ?string $coverImage = null;
}
