<?php

declare(strict_types=1);

namespace App\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;

class UserMagicRegisterInput
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 180)]
    public string $username;

    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    #[Assert\NotBlank]
    #[Assert\Regex('/\+?\d+/')]
    public string $phoneNumber;

    #[Assert\Url]
    public ?string $profilePicture = null;
}
