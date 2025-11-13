<?php

declare(strict_types=1);

namespace App\ApiResource\Artist;

use Symfony\Component\Validator\Constraints as Assert;

class ArtistSourceDto
{
    #[Assert\NotBlank]
    #[Assert\Choice(
        choices: ['spotify', 'deezer', 'soundcloud'],
        message: 'Invalid platform. Allowed: spotify, deezer, soundcloud'
    )]
    public string $platform;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $platformArtistId;

    public function __construct(string $platform = '', string $platformArtistId = '')
    {
        $this->platform = $platform;
        $this->platformArtistId = $platformArtistId;
    }
}
