<?php

declare(strict_types=1);

namespace App\ApiResource\Post;

use ApiPlatform\Metadata\ApiProperty;
use App\ApiResource\Track\TrackGetOutput;
use App\Entity\User;

class PostGetOutput
{
    #[ApiProperty(example: 1)]
    public int $id;

    public User $user;

    #[ApiProperty(example: 'Amazing sunset vibes! 🌅 #music #vibes')]
    public ?string $caption;

    public TrackGetOutput $track;

    #[ApiProperty(example: 'https://example.com/photos/sunset-beach.jpg')]
    public ?string $photoUrl;

    #[ApiProperty(example: 'Paris, France')]
    public ?string $location;

    #[ApiProperty(example: '2025-08-25T10:30:00Z')]
    public ?\DateTimeInterface $createdAt;

    #[ApiProperty(example: '2025-08-25T15:45:00Z')]
    public ?\DateTimeInterface $updatedAt;

    public static function fromEntity(\App\Entity\Post $post): self
    {
        $output = new self();
        $output->id = $post->getId();
        $output->user = $post->getUser();
        $output->caption = $post->getCaption();
        $output->track = TrackGetOutput::fromEntity($post->getTrack());
        $output->photoUrl = $post->getPhotoUrl();
        $output->location = $post->getLocation();
        $output->createdAt = $post->getCreatedAt();
        $output->updatedAt = $post->getUpdatedAt();

        return $output;
    }
}