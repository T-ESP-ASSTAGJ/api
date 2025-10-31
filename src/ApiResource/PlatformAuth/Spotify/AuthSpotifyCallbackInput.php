<?php

declare(strict_types=1);

namespace App\ApiResource\PlatformAuth\Spotify;

use Symfony\Component\Validator\Constraints as Assert;

class AuthSpotifyCallbackInput
{
    #[Assert\NotBlank]
    public ?string $code = null;
}
