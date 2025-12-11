<?php

declare(strict_types=1);

namespace App\ApiResource\Track;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Validator\Constraints as Assert;

class TrackCreateInput
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[ApiProperty(example: 'Blinding Lights')]
    public string $title;

    #[Assert\Url]
    #[ApiProperty(example: 'https://i.scdn.co/image/ab67616d0000b273123456789abcdef')]
    public ?string $coverUrl = null;

    /**
     * @var array<string, mixed>
     */
    #[Assert\Type('array')]
    #[ApiProperty(example: [
        'album' => 'After Hours',
        'duration' => 200,
        'genre' => 'Pop',
        'isrc' => 'USUG12000193',
        'releaseDate' => '2020-03-20',
    ])]
    public array $metadata = [];

    #[Assert\NotBlank]
    #[Assert\Positive]
    #[ApiProperty(example: 1)]
    public int $artistId;

    /**
     * @var TrackSourceDto[]
     */
    #[Assert\Type('array')]
    #[Assert\Valid]
    #[ApiProperty(example: [
        [
            'platform' => 'spotify',
            'platformTrackId' => '4uLU6hMCjMI75M1A2tKUQC',
            'metadata' => [
                'popularity' => 85,
                'rank' => 1,
                'explicit' => false,
                'previewUrl' => 'https://p.scdn.co/mp3-preview/xxx',
            ],
        ],
    ])]
    public array $trackSources = [];
}
