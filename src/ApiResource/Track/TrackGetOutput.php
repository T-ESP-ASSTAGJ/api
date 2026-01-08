<?php

declare(strict_types=1);

namespace App\ApiResource\Track;

use ApiPlatform\Metadata\ApiProperty;

class TrackGetOutput
{
    #[ApiProperty(example: 1)]
    public int $id;

    #[ApiProperty(example: 'spotify:track:123456789')]
    public string $songId;

    #[ApiProperty(example: 'Blinding Lights')]
    public string $title;

    #[ApiProperty(example: 'The Weeknd')]
    public string $artistName;

    #[ApiProperty(example: 2020)]
    public ?int $releaseYear;

    #[ApiProperty(example: 'https://i.scdn.co/image/ab67616d0000b273123456789abcdef')]
    public ?string $coverImage;

    #[ApiProperty(example: '2025-08-25T10:30:00Z')]
    public ?\DateTimeInterface $createdAt;

    #[ApiProperty(example: '2025-08-25T15:45:00Z')]
    public ?\DateTimeInterface $updatedAt;

    public static function fromEntity(\App\Entity\Track $track): self
    {
        $output = new self();
        $output->id = $track->getId();
        $output->songId = $track->getSongId();
        $output->title = $track->getTitle();
        $output->artistName = $track->getArtistName();
        $output->releaseYear = $track->getReleaseYear();
        $output->coverImage = $track->getCoverImage();
        $output->createdAt = $track->getCreatedAt();
        $output->updatedAt = $track->getUpdatedAt();

        return $output;
    }
}
