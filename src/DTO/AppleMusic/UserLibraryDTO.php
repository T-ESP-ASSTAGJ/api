<?php

declare(strict_types=1);

namespace App\DTO\AppleMusic;

class UserLibraryDTO
{
    public function __construct(
        public readonly array $songs,
        public readonly array $playlists,
        public readonly array $albums
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            songs: array_map(fn($song) => SongDTO::fromArray($song), $data['songs'] ?? []),
            playlists: array_map(fn($playlist) => PlaylistDTO::fromArray($playlist), $data['playlists'] ?? []),
            albums: array_map(fn($album) => AlbumDTO::fromArray($album), $data['albums'] ?? [])
        );
    }
}