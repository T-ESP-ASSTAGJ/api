<?php

declare(strict_types=1);

namespace App\ApiResource\Message;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @codeCoverageIgnore
 */
class MessageCreateInput
{
    #[Assert\NotNull]
    public ?int $conversationId = null;
    #[Assert\NotNull]
    public ?string $type = null;
    #[Assert\NotNull]
    public ?string $content = null;
}
