<?php

declare(strict_types=1);

namespace App\ApiResource\PlatformAuth\Spotify;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Entity\Token;
use App\State\Spotify\AuthSpotifyCallbackProvider;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/spotify/callback',
            shortName: 'Spotify',
            output: AuthSpotifyCallbackOutput::class,
            provider: AuthSpotifyCallbackProvider::class
        ),
    ],
    provider: null
)]
class AuthSpotifyCallbackOutput
{
    public bool $success;
    public string $platform = Token::PLATFORM_SPOTIFY;
    public ?string $expires_at = null;
    public ?string $message = null;
    public ?string $error = null;
    public ?string $details = null;
}
