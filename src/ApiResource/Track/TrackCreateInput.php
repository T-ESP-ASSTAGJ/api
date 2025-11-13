<?php

declare(strict_types=1);

namespace App\ApiResource\Track;

use Symfony\Component\Validator\Constraints as Assert;

class TrackCreateInput
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $title;

    #[Assert\Url]
    public ?string $coverUrl = null;

    /**
     * @var array<string, mixed>
     */
    #[Assert\Type('array')]
    public array $metadata = [];

    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $artistId;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $length;
}
