<?php

declare(strict_types=1);

namespace App\Service\Spotify;

use App\DTO\Spotify\UserProfileDTO;
use App\DTO\Spotify\TrackDTO;
use App\DTO\Spotify\PlaylistDTO;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SpotifyService
{
    public function __construct(
        private readonly HttpClientInterface $spotifyApiClient
    ) {}

    public function getUserProfile(string $accessToken): UserProfileDTO
    {
        try {
            $response = $this->spotifyApiClient->request('GET', '/me', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);

            return UserProfileDTO::fromArray($response->toArray());
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to get user profile: ' . $e->getMessage());
        }
    }

    public function getUserPlaylists(string $accessToken, int $limit = 20): array
    {
        try {
            $response = $this->spotifyApiClient->request('GET', '/me/playlists', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
                'query' => [
                    'limit' => $limit,
                ],
            ]);

            $data = $response->toArray();
            return array_map(fn($item) => PlaylistDTO::fromArray($item), $data['items'] ?? []);
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to get user playlists: ' . $e->getMessage());
        }
    }

    public function searchMusic(string $accessToken, string $query, string $type = 'track', int $limit = 20): array
    {
        try {
            $response = $this->spotifyApiClient->request('GET', '/search', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
                'query' => [
                    'q' => $query,
                    'type' => $type,
                    'limit' => $limit,
                ],
            ]);

            $data = $response->toArray();
            
            if ($type === 'track' && isset($data['tracks']['items'])) {
                return array_map(fn($item) => TrackDTO::fromArray($item), $data['tracks']['items']);
            }
            
            return $data;
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to search music: ' . $e->getMessage());
        }
    }

    public function getTrack(string $accessToken, string $trackId): TrackDTO
    {
        try {
            $response = $this->spotifyApiClient->request('GET', '/tracks/' . $trackId, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);

            return TrackDTO::fromArray($response->toArray());
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to get track: ' . $e->getMessage());
        }
    }

    public function getAlbum(string $accessToken, string $albumId): array
    {
        try {
            $response = $this->spotifyApiClient->request('GET', '/albums/' . $albumId, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);

            return $response->toArray();
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to get album: ' . $e->getMessage());
        }
    }

    public function getArtist(string $accessToken, string $artistId): array
    {
        try {
            $response = $this->spotifyApiClient->request('GET', '/artists/' . $artistId, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);

            return $response->toArray();
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to get artist: ' . $e->getMessage());
        }
    }
}