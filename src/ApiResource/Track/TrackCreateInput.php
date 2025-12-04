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
        'platform' => 'spotify',
        'platformId' => '4uLU6hMCjMI75M1A2tKUQC',
        'externalUrl' => 'https://open.spotify.com/track/4uLU6hMCjMI75M1A2tKUQC',
        'isrc' => 'USUG12000193',
        'previewUrl' => 'https://p.scdn.co/mp3-preview/9af4e9e0b1e9e2e8e3e4e5e6e7e8e9f0',
        'releaseDate' => '01/11/2000'
    ])]
    public array $metadata = [];

    #[Assert\NotBlank]
    #[Assert\Positive]
    #[ApiProperty(example: 1)]
    public int $artistId;
}