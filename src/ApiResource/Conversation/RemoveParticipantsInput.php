<?php

declare(strict_types=1);

namespace App\ApiResource\Conversation;

use Symfony\Component\Validator\Constraints as Assert;

readonly class RemoveParticipantsInput
{
    public function __construct(
        /**
         * @var array<int> Array of user IDs to remove
         */
        #[Assert\NotBlank(message: 'Vous devez fournir au moins un participant à retirer')]
        #[Assert\Type('array')]
        #[Assert\Count(min: 1, minMessage: 'Vous devez sélectionner au moins un participant à retirer')]
        public array $userIds = [],
    ) {
    }
}
