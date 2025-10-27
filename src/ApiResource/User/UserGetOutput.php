<?php

declare(strict_types=1);

namespace App\ApiResource\User;

class UserGetOutput
{
    public string $email;

    public ?string $username = null;

    /**
     * @var array <string>
     */
    public array $roles;

    public ?string $bio = null;
    public ?string $profilePicture = null;
    public bool $isVerified;
}
