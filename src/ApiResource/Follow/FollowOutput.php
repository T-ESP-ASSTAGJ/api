<?php

declare(strict_types=1);

namespace App\ApiResource\Follow;

use Symfony\Component\Validator\Constraints as Assert;

final class FollowOutput
{
    #[Assert\NotNull]
    public string $message;
}
