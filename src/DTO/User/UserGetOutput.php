<?php

declare(strict_types=1);

namespace App\DTO\User;

use ApiPlatform\Metadata\ApiProperty;

class UserGetOutput
{
    #[ApiProperty(example: 1)]
    public int $id;

    #[ApiProperty(example: 'John')]
    public string $username;

    #[ApiProperty(example: 'john@doe.com')]
    public string $email;

    #[ApiProperty(example: 'ROLE_USER')]
    public array $roles;
}
