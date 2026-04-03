<?php

declare(strict_types=1);

namespace App\ApiResource\Report;

use App\Entity\Enum\ReportableTypeEnum;
use App\Entity\Enum\ReportReasonEnum;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @codeCoverageIgnore
 */
class ReportCreateInput
{
    #[Assert\NotBlank]
    public ReportableTypeEnum $entityClass;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $entityId;

    #[Assert\NotBlank]
    public ReportReasonEnum $reason;

    #[Assert\Length(max: 500)]
    public ?string $message = null;
}
