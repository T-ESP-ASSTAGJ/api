<?php

declare(strict_types=1);

namespace App\ApiResource\User;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @codeCoverageIgnore
 */
class UserPatchInput
{
    public ?string $username = null;

    #[Assert\Regex(pattern: '/^\+?\d{1,19}$/', message: 'Invalid phone number format')]
    #[ApiProperty(openapiContext: ['example' => '+33612345678'])]
    public ?string $phoneNumber = null;

    public ?string $profilePicture = null;

    public ?string $bio = null;
}
