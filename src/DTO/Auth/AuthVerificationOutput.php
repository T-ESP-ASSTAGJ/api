<?php

declare(strict_types=1);

namespace App\DTO\Auth;

use Symfony\Component\Validator\Constraints as Assert;

class AuthVerificationOutput
{
    #[Assert\NotBlank]
    public string $message;

    public ?bool $needsProfile = null;

    #[Assert\NotBlank]
    public bool $isVerified;

    public ?string $token = null;
}
