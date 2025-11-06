<?php

declare(strict_types=1);

namespace App\DTO\Artist;

use Symfony\Component\Validator\Constraints as Assert;

class ArtistUpdateInput
{
    #[Assert\Length(max: 255)]
    public ?string $name = null;

    /**
     * @var array<string, mixed>|null
     */
    #[Assert\Type('array')]
    public ?array $metadata = null;
}