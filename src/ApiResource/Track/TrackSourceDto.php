<?php

declare(strict_types=1);

namespace App\ApiResource\Track;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Validator\Constraints as Assert;

class TrackSourceDto
{
    #[Assert\NotBlank]
    #[Assert\Choice(
        choices: ['spotify', 'deezer', 'soundcloud', 'apple_music'],
        message: 'Invalid platform. Allowed: spotify, deezer, soundcloud, apple_music'
    )]
    #[ApiProperty(example: 'spotify')]
    public string $platform;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[ApiProperty(example: '4uLU6hMCjMI75M1A2tKUQC')]
    public string $platformTrackId;

    /**
     * @var array<string, mixed>
     */
    #[Assert\Type('array')]
    #[ApiProperty(example: [
        'popularity' => 85,
        'rank' => 1,
        'explicit' => false,
        'preview_url' => 'https://p.scdn.co/mp3-preview/xxx',
    ])]
    public array $metadata = [];

    public function __construct(string $platform = '', string $platformTrackId = '', array $metadata = [])
    {
        $this->platform = $platform;
        $this->platformTrackId = $platformTrackId;
        $this->metadata = $metadata;
    }
}
