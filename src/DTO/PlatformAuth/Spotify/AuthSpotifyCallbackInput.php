<?php

declare(strict_types=1);

namespace App\DTO\PlatformAuth\Spotify;

class AuthSpotifyCallbackInput
{
    public ?string $code = null;
    public ?string $error = null;
    public ?string $state = null;
}
