<?php

declare(strict_types=1);

namespace App\DTO\Post;

use Symfony\Component\Validator\Constraints as Assert;

class PostCreateInput
{
    #[Assert\NotBlank]
    public int $userId;

    #[Assert\Url]
    #[Assert\Length(max: 500)]
    public ?string $songPreviewUrl = null;

    #[Assert\Length(max: 1000)]
    public ?string $caption = null;

    /**
     * @var array{
     *     title: string,
     *     artist: string,
     *     album: string,
     *     duration: int,
     *     genre: string,
     *     platform: string,
     *     platform_id: string,
     *     external_url: string,
     *     isrc: string
     * }
     */
    #[Assert\NotBlank]
    #[Assert\Type('array')]
    public array $track;

    #[Assert\Url]
    #[Assert\Length(max: 500)]
    public ?string $photoUrl = null;

    #[Assert\Length(max: 255)]
    public ?string $location = null;
}
