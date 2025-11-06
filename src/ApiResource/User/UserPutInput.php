<?php

declare(strict_types=1);

namespace App\ApiResource\User;

use Symfony\Component\Validator\Constraints as Assert;

class UserPutInput
{
    public ?string $username = null;
    #[Assert\Regex('/\+?\d+/')]
    public ?string $phoneNumber = null;
    public ?string $profilePicture = null;
    public ?string $bio = null;
}
