<?php

declare(strict_types=1);

namespace App\DTO\Artist;

use Symfony\Component\Validator\Constraints as Assert;

class ArtistCreateInput
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $name;

    /**
     * @var array<string, mixed>
     */
    #[Assert\Type('array')]
    public array $metadata = [];
}