<?php

declare(strict_types=1);

namespace App\ApiResource\PlatformAuth\SoundCloud;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Entity\Token;
use App\State\SoundCloud\AuthSoundCloudCallbackProvider;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/soundcloud/callback',
            shortName: 'SoundCloud',
            output: AuthSoundCloudCallbackOutput::class,
            provider: AuthSoundCloudCallbackProvider::class
        ),
    ],
    provider: null
)]
class AuthSoundCloudCallbackOutput
{
    public bool $success;
    public string $platform = Token::PLATFORM_SOUNDCLOUD;
    public ?string $expires_at = null;
    public ?string $message = null;
    public ?string $error = null;
    public ?string $details = null;
}