<?php

declare(strict_types=1);

namespace App\ApiResource\Like;

use App\Entity\Enum\LikeableTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;

class LikeCreateInput
{
    #[Assert\NotBlank]
    public LikeableTypeEnum $entityClass;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $entityId;
}