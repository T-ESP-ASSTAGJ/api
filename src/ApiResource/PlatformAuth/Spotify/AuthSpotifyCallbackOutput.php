<?php

declare(strict_types=1);

namespace App\ApiResource\PlatformAuth\Spotify;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\State\Spotify\AuthSpotifyCallbackProcessor;
use Symfony\Component\Validator\Constraints as Assert;

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

    #[Assert\NotBlank]
    public string $platform;

    public ?string $expires_at = null;
    public ?string $message = null;
    public ?string $error = null;
    public ?string $details = null;
}
