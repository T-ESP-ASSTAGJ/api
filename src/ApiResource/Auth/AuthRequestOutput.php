<?php

declare(strict_types=1);

namespace App\ApiResource\Auth;

use Symfony\Component\Validator\Constraints as Assert;

class AuthRequestOutput
{
    #[Assert\NotBlank]
    public string $message;
}
