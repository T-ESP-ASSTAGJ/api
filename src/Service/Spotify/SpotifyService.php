<?php

declare(strict_types=1);

namespace App\Service\Spotify;

use App\DTO\Spotify\AlbumDTO;
use App\DTO\Spotify\ArtistDTO;
use App\DTO\Spotify\PlaylistDTO;
use App\DTO\Spotify\TrackDTO;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class SpotifyService
{
    public function __construct(
        private HttpClientInterface $spotifyApiClient,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    /** @return PlaylistDTO[] */
    public function getUserPlaylists(string $accessToken, int $limit = 20): array
    {
        try {
            $response = $this->spotifyApiClient->request('GET', '/me/playlists', [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                ],
                'query' => [
                    'limit' => $limit,
                ],
            ]);

            $data = $response->toArray();

            return array_map(fn ($item) => $this->objectMapper->map((object) $item, PlaylistDTO::class), $data['items'] ?? []);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to get user playlists: '.$e->getMessage());
        }
    }

    /** @return TrackDTO[] */
    public function searchMusic(string $accessToken, string $query, string $type = 'track', int $limit = 20): array
    {
        try {
            $response = $this->spotifyApiClient->request('GET', '/search', [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                ],
                'query' => [
                    'q' => $query,
                    'type' => $type,
                    'limit' => $limit,
                ],
            ]);

            $data = $response->toArray();

            if ('track' === $type && isset($data['tracks']['items'])) {
                return array_map(fn ($item) => $this->objectMapper->map((object) $item, TrackDTO::class), $data['tracks']['items']);
            }

            return $data;
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to search music: '.$e->getMessage());
        }
    }

    public function getTrack(string $accessToken, string $trackId): TrackDTO
    {
        try {
            $response = $this->spotifyApiClient->request('GET', '/tracks/'.$trackId, [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                ],
            ]);

            $data = $response->toArray();

            return $this->objectMapper->map((object) $data, TrackDTO::class);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to get track: '.$e->getMessage());
        }
    }

    public function getAlbum(string $accessToken, string $albumId): AlbumDTO
    {
        try {
            $response = $this->spotifyApiClient->request('GET', '/albums/'.$albumId, [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                ],
            ]);

            $data = $response->toArray();

            return $this->objectMapper->map((object) $data, AlbumDTO::class);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to get album: '.$e->getMessage());
        }
    }

    public function getArtist(string $accessToken, string $artistId): ArtistDTO
    {
        try {
            $response = $this->spotifyApiClient->request('GET', '/artists/'.$artistId, [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                ],
            ]);

            $data = $response->toArray();

            return $this->objectMapper->map((object) $data, ArtistDTO::class);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to get artist: '.$e->getMessage());
        }
    }
}
