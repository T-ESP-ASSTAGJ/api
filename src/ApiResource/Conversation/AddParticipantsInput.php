<?php

declare(strict_types=1);

namespace App\ApiResource\Conversation;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Validator\Constraints as Assert;

readonly class AddParticipantsInput
{
    public function __construct(
        /**
         * @var array<int> Tableau des identifiants d'utilisateurs à ajouter
         */
        #[Assert\NotBlank(message: 'Vous devez fournir au moins un participant')]
        #[Assert\Type('array')]
        #[Assert\Count(min: 1, minMessage: 'Vous devez sélectionner au moins un participant')]
        #[ApiProperty(
            openapiContext: [
                'type' => 'array',
                'items' => ['type' => 'integer'],
            ],
        )]
        public array $userIds = [],
    ) {
    }
}
