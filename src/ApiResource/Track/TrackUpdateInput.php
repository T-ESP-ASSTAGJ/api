<?php

declare(strict_types=1);

namespace App\ApiResource\Track;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Validator\Constraints as Assert;

class TrackUpdateInput
{
    #[Assert\Length(max: 255)]
    #[ApiProperty(example: 'Blinding Lights')]
    public ?string $title = null;

    #[Assert\Url]
    #[ApiProperty(example: 'https://i.scdn.co/image/ab67616d0000b273123456789abcdef')]
    public ?string $coverUrl = null;

    /**
     * @var array<string, mixed>|null
     */
    #[Assert\Type('array')]
    #[ApiProperty(example: [
        'album' => 'After Hours',
        'duration' => 200,
        'genre' => 'Pop',
        'isrc' => 'USUG12000193',
        'releaseDate' => '2020-03-20',
    ])]
    public ?array $metadata = null;

    #[Assert\Positive]
    #[ApiProperty(example: 1)]
    public ?int $artistId = null;

    /**
     * @var TrackSourceDto[]|null
     */
    #[Assert\Type('array')]
    #[Assert\Valid]
    public ?array $trackSources = null;
}
