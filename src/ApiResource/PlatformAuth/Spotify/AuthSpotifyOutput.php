<?php

declare(strict_types=1);

namespace App\ApiResource\PlatformAuth\Spotify;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\State\Spotify\AuthSpotifyProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/auth/spotify/authorize',
            shortName: 'AuthSpotify',
            input: false,
            output: AuthSpotifyOutput::class,
            processor: AuthSpotifyProcessor::class
        ),
    ],
    provider: null
)]
class AuthSpotifyOutput
{
    #[Assert\NotBlank]
    public ?string $authorization_url = null;

    #[Assert\NotBlank]
    public string $platform;

    #[Assert\NotBlank]
    public string $message;
}
