<?php

declare(strict_types=1);

namespace App\ApiResource\Auth;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\State\Auth\AuthRequestProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/auth/request',
            input: AuthRequestInput::class,
            output: AuthRequestOutput::class,
            name: 'auth_request',
            provider: null,
            processor: AuthRequestProcessor::class
        ),
    ]
)]
class AuthRequestInput
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;
}
