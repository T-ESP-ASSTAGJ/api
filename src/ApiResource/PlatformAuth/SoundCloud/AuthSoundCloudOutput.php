<?php

declare(strict_types=1);

namespace App\ApiResource\PlatformAuth\SoundCloud;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\State\SoundCloud\AuthSoundCloudProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/soundcloud/authorize',
            shortName: 'SoundCloud',
            input: false,
            output: AuthSoundCloudOutput::class,
            processor: AuthSoundCloudProcessor::class
        ),
    ],
)]
class AuthSoundCloudOutput
{
    #[Assert\NotBlank]
    public string $authorization_url;

    #[Assert\NotBlank]
    public string $platform;

    #[Assert\NotBlank]
    public string $message;
}