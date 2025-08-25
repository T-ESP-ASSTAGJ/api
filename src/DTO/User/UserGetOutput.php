<?php

declare(strict_types=1);

namespace App\DTO\User;

use ApiPlatform\Metadata\ApiProperty;

class UserGetOutput
{
    #[ApiProperty(example: 'John')]
    public string $username;

    #[ApiProperty(example: 'john@doe.com')]
    public string $email;

    /**
     * @var array <string>
     */
    #[ApiProperty(example: 'ROLE_USER')]
    public array $roles;
}
