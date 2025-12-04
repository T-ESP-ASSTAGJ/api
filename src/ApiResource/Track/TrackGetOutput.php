<?php

declare(strict_types=1);

namespace App\ApiResource\Track;

use ApiPlatform\Metadata\ApiProperty;
use App\Entity\Artist;

class TrackGetOutput
{
    #[ApiProperty(example: 1)]
    public int $id;

    #[ApiProperty(example: 'Blinding Lights')]
    public string $title;

    #[ApiProperty(example: 'https://i.scdn.co/image/ab67616d0000b273123456789abcdef')]
    public ?string $coverUrl;

    public Artist $artist;

    /**
     * @var array<string, mixed>
     */
    #[ApiProperty(example: [
        'album' => 'After Hours',
        'duration' => 200,
        'genre' => 'Pop',
        'platform' => 'spotify',
        'platformId' => '4uLU6hMCjMI75M1A2tKUQC',
        'externalUrl' => 'https://open.spotify.com/track/4uLU6hMCjMI75M1A2tKUQC',
        'isrc' => 'USUG12000193',
        'previewUrl' => 'https://p.scdn.co/mp3-preview/9af4e9e0b1e9e2e8e3e4e5e6e7e8e9f0',
    ])]
    public array $metadata;

    #[ApiProperty(example: '2025-08-25T10:30:00Z')]
    public ?\DateTimeInterface $createdAt;

    #[ApiProperty(example: '2025-08-25T15:45:00Z')]
    public ?\DateTimeInterface $updatedAt;

    public static function fromEntity(\App\Entity\Track $track): self
    {
        $output = new self();
        $output->id = $track->getId();
        $output->title = $track->getTitle();
        $output->coverUrl = $track->getCoverUrl();
        $output->artist = $track->getArtist();
        $output->metadata = $track->getMetadata();
        $output->createdAt = $track->getCreatedAt();
        $output->updatedAt = $track->getUpdatedAt();

        return $output;
    }
}