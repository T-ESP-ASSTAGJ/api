<?php

declare(strict_types=1);

namespace App\DTO\SoundCloud;

class TrackDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $username,
        public readonly string $userId,
        public readonly int $duration,
        public readonly string $streamUrl,
        public readonly ?string $artworkUrl,
        public readonly string $permalinkUrl,
        public readonly int $playbackCount,
        public readonly int $likesCount
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (string)$data['id'],
            title: $data['title'] ?? '',
            username: $data['user']['username'] ?? '',
            userId: (string)($data['user']['id'] ?? ''),
            duration: $data['duration'] ?? 0,
            streamUrl: $data['stream_url'] ?? '',
            artworkUrl: $data['artwork_url'] ?? null,
            permalinkUrl: $data['permalink_url'] ?? '',
            playbackCount: $data['playback_count'] ?? 0,
            likesCount: $data['likes_count'] ?? 0
        );
    }
}