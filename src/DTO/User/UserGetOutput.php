<?php

declare(strict_types=1);

namespace App\DTO\User;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource]
class UserGetOutput
{
    public string $username;

    /**
     * @var array <string>
     */
    public array $roles;

    public ?string $bio;
    public ?string $profilePicture = null;
    public bool $isConfirmed;
}
