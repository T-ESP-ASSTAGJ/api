<?php

declare(strict_types=1);

namespace App\ApiResource\Auth;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use App\State\Auth\AuthVerifyProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/auth/verify',
            openapi: new Operation(
                summary: 'Verify User',
                requestBody: new RequestBody(
                    content: new \ArrayObject(
                        [
                            'application/json' => [
                                'schema' => [
                                    'properties' => [
                                        'email' => ['type' => 'string', 'required' => true, 'description' => 'email'],
                                        'code' => ['type' => 'string', 'required' => true, 'description' => 'code'],
                                    ],
                                ],
                                'example' => [
                                    'email' => 'user@example.com',
                                    'code' => '123456',
                                ],
                            ],
                        ]
                    )
                )
            ),
            shortName: 'Auth',
            input: AuthVerificationInput::class,
            output: AuthVerificationOutput::class,
            processor: AuthVerifyProcessor::class
        ),
    ]
)]
readonly class AuthVerificationInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public ?string $email = null,

        #[Assert\NotBlank]
        #[Assert\Regex('/^[0-9]{6}$/')]
        public ?string $code = null,
    ) {
    }
}
