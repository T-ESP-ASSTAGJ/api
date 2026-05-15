<?php

declare(strict_types=1);

namespace App\ApiResource\Artist;

use Symfony\Component\Validator\Constraints as Assert;

class ArtistUpdateInput
{
    #[Assert\Length(max: 255)]
    public ?string $name = null;

    /**
     * @var ArtistSourceDto[]|null
     */
    #[Assert\Type('array')]
    #[Assert\Valid]
    public ?array $artistSources = null;
}
