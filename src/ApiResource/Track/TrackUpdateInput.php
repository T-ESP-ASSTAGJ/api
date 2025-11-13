<?php

declare(strict_types=1);

namespace App\ApiResource\Track;

use Symfony\Component\Validator\Constraints as Assert;

class TrackUpdateInput
{
    #[Assert\Length(max: 255)]
    public ?string $title = null;

    #[Assert\Url]
    public ?string $coverUrl = null;

    /**
     * @var array<string, mixed>|null
     */
    #[Assert\Type('array')]
    public ?array $metadata = null;

    #[Assert\Positive]
    public ?int $artistId = null;

    #[Assert\Positive]
    public ?int $length = null;
}
