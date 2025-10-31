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
            shortName: 'Auth',
            input: AuthRequestInput::class,
            output: AuthRequestOutput::class,
            provider: null,
            processor: AuthRequestProcessor::class
        ),
    ]
)]
readonly class AuthRequestInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,
    ) {
    }
}
