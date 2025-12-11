<?php

declare(strict_types=1);

namespace App\ApiResource\Comment;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Validator\Constraints as Assert;

class CommentCreateInput
{
    #[Assert\NotBlank(message: 'Le commentaire ne peut pas être vide')]
    #[Assert\Length(max: 2000)]
    #[ApiProperty(example: 'Super morceau ! J\'adore cette mélodie.')]
    public string $content;
}
