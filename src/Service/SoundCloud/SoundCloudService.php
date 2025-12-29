<?php

declare(strict_types=1);

namespace App\Service\SoundCloud;

use App\ApiResource\SoundCloud\PlaylistDTO;
use App\ApiResource\SoundCloud\TrackDTO;
use App\ApiResource\SoundCloud\UserDTO;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class SoundCloudService
{
    public function __construct(
        private HttpClientInterface $soundcloudApiClient,
    ) {
    }

    /** @return PlaylistDTO[] */
    public function getUserPlaylists(string $accessToken, int $limit = 20): array
    {
        try {
            $response = $this->soundcloudApiClient->request('GET', '/me/playlists', [
                'headers' => [
                    'Authorization' => 'OAuth '.$accessToken,
                ],
                'query' => [
                    'limit' => $limit,
                ],
            ]);

            $data = $response->toArray();

            return array_map(
                fn (array $item) => $this->mapToPlaylistDTO($item),
                $data['collection'] ?? []
            );
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to get user playlists: '.$e->getMessage());
        }
    }

    /** @return TrackDTO[] */
    public function searchMusic(string $accessToken, string $query, int $limit = 20): array
    {
        try {
            $response = $this->soundcloudApiClient->request('GET', '/tracks', [
                'headers' => [
                    'Authorization' => 'OAuth '.$accessToken,
                ],
                'query' => [
                    'q' => $query,
                    'limit' => $limit,
                ],
            ]);

            $data = $response->toArray();

            return array_map(
                fn (array $item) => $this->mapToTrackDTO($item),
                $data['collection'] ?? []
            );
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to search music: '.$e->getMessage());
        }
    }

    public function getTrack(string $accessToken, string $trackId): TrackDTO
    {
        try {
            $response = $this->soundcloudApiClient->request('GET', '/tracks/'.$trackId, [
                'headers' => [
                    'Authorization' => 'OAuth '.$accessToken,
                ],
            ]);

            $data = $response->toArray();

            return $this->mapToTrackDTO($data);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to get track: '.$e->getMessage());
        }
    }

    public function getUser(string $accessToken, string $userId): UserDTO
    {
        try {
            $response = $this->soundcloudApiClient->request('GET', '/users/'.$userId, [
                'headers' => [
                    'Authorization' => 'OAuth '.$accessToken,
                ],
            ]);

            $data = $response->toArray();

            return $this->mapToUserDTO($data);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to get user: '.$e->getMessage());
        }
    }

    public function getPlaylist(string $accessToken, string $playlistId): PlaylistDTO
    {
        try {
            $response = $this->soundcloudApiClient->request('GET', '/playlists/'.$playlistId, [
                'headers' => [
                    'Authorization' => 'OAuth '.$accessToken,
                ],
            ]);

            $data = $response->toArray();

            return $this->mapToPlaylistDTO($data);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to get playlist: '.$e->getMessage());
        }
    }

    private function mapToUserDTO(array $data): UserDTO
    {
        return new UserDTO(
            id: $data['id'],
            username: $data['username'] ?? '',
            permalink: $data['permalink'] ?? '',
            permalink_url: $data['permalink_url'] ?? '',
            avatar_url: $data['avatar_url'] ?? null,
            country: $data['country'] ?? null,
            city: $data['city'] ?? null,
            description: $data['description'] ?? null,
            followers_count: $data['followers_count'] ?? 0,
            followings_count: $data['followings_count'] ?? 0,
            track_count: $data['track_count'] ?? 0,
            playlist_count: $data['playlist_count'] ?? 0,
        );
    }

    private function mapToTrackDTO(array $data): TrackDTO
    {
        return new TrackDTO(
            id: $data['id'],
            title: $data['title'] ?? '',
            description: $data['description'] ?? null,
            duration: $data['duration'] ?? 0,
            permalink_url: $data['permalink_url'] ?? '',
            artwork_url: $data['artwork_url'] ?? null,
            playback_count: $data['playback_count'] ?? 0,
            likes_count: $data['likes_count'] ?? 0,
            streamable: $data['streamable'] ?? false,
            download_url: $data['download_url'] ?? null,
            user: $this->mapToUserDTO($data['user'] ?? []),
            created_at: $data['created_at'] ?? '',
            genre: $data['genre'] ?? null,
            tag_list: $data['tag_list'] ?? null,
        );
    }

    private function mapToPlaylistDTO(array $data): PlaylistDTO
    {
        $tracks = null;
        if (isset($data['tracks']) && is_array($data['tracks'])) {
            $tracks = array_map(
                fn (array $track) => $this->mapToTrackDTO($track),
                $data['tracks']
            );
        }

        return new PlaylistDTO(
            id: $data['id'],
            title: $data['title'] ?? '',
            description: $data['description'] ?? null,
            duration: $data['duration'] ?? 0,
            permalink_url: $data['permalink_url'] ?? '',
            artwork_url: $data['artwork_url'] ?? null,
            user: $this->mapToUserDTO($data['user'] ?? []),
            created_at: $data['created_at'] ?? '',
            track_count: $data['track_count'] ?? 0,
            tracks: $tracks,
            is_public: $data['public'] ?? true,
            genre: $data['genre'] ?? null,
            tag_list: $data['tag_list'] ?? null,
        );
    }
}