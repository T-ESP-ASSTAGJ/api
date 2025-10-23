<?php

declare(strict_types=1);

namespace App\DTO\GroupMessage;

readonly class GroupMessageGetOutput
{
    public function __construct(
        public int $id,
        public int $groupId,
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
