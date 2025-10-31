<?php

declare(strict_types=1);

namespace App\ApiResource\Message;

readonly class MessageGetOutput
{
    public function __construct(
        public int $id,
        public string $type,
        public ?string $content,
        /**
         * @var array{
         *     title: string,
         *     artist: string,
         *     album_cover: string,
         *     preview_url: string|null,
         *     platform_link: string,
         *     availability: string
         * }|null
         */
        public ?array $trackMetadata,
        /**
         * @var array{
         *     id: int,
         *     username: string
         * }
         */
        public array $author,
        public string $createdAt,
    ) {
    }
}
