<?php

declare(strict_types=1);

namespace App\ApiResource\Conversation;

use Symfony\Component\Validator\Constraints as Assert;

readonly class AddParticipantsInput
{
    public function __construct(
        /**
         * @var array<int> Array of user IDs to add
         */
        #[Assert\NotBlank(message: 'Vous devez fournir au moins un participant')]
        #[Assert\Type('array')]
        #[Assert\Count(min: 1, minMessage: 'Vous devez sélectionner au moins un participant')]
        public array $userIds = [],
    ) {
    }
}
