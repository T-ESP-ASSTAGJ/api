<?php

declare(strict_types=1);

namespace App\ApiResource\Artist;

use Symfony\Component\Validator\Constraints as Assert;

class ArtistCreateInput
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $name;

    /**
     * @var ArtistSourceDto[]
     */
    #[Assert\Type('array')]
    #[Assert\Valid]
    public array $artistSources = [];
}
