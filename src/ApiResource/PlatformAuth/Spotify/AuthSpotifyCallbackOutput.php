<?php

declare(strict_types=1);

namespace App\ApiResource\PlatformAuth\Spotify;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Entity\Token;
use App\State\Spotify\AuthSpotifyCallbackProcessor;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/auth/spotify/callback',
            shortName: 'AuthSpotify',
            input: AuthSpotifyCallbackInput::class,
            output: AuthSpotifyCallbackOutput::class,
            processor: AuthSpotifyCallbackProcessor::class
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
