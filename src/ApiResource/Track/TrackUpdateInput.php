<?php

declare(strict_types=1);

namespace App\ApiResource\Track;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @codeCoverageIgnore
 */
class TrackUpdateInput
{
    #[Assert\Length(max: 255)]
    #[ApiProperty(example: '1234567890')]
    public ?string $songId = null;

    #[Assert\Length(max: 255)]
    #[ApiProperty(example: 'Blinding Lights')]
    public ?string $title = null;

    #[Assert\Length(max: 255)]
    #[ApiProperty(example: 'The Weeknd')]
    public ?string $artistName = null;

    #[Assert\Positive]
    #[ApiProperty(example: 2020)]
    public ?int $releaseYear = null;
}
