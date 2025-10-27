<?php

declare(strict_types=1);

namespace App\ApiResource\Follow;

use Symfony\Component\Validator\Constraints as Assert;

final class FollowInput
{
    #[Assert\NotNull]
    public ?int $userId = null;
}