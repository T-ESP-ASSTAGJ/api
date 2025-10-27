<?php

declare(strict_types=1);

namespace App\ApiResource\Auth;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\State\Auth\AuthVerifyProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/auth/verify',
            shortName: 'Auth',
            input: AuthVerificationInput::class,
            output: AuthVerificationOutput::class,
            processor: AuthVerifyProcessor::class
        ),
    ]
)]
class AuthVerificationInput
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email = null;

    #[Assert\NotBlank]
    #[Assert\Regex('/^[0-9]{6}$/')]
    public ?string $code = null;
}
