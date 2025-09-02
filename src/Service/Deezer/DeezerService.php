<?php

declare(strict_types=1);

namespace App\Service\Deezer;

use App\DTO\Deezer\UserProfileDTO;
use App\DTO\Deezer\TrackDTO;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DeezerService
{
    public function __construct(
        private readonly HttpClientInterface $deezerApiClient
    ) {}

    public function getUserProfile(string $accessToken): UserProfileDTO
    {
        try {
            $response = $this->deezerApiClient->request('GET', '/user/me', [
                'query' => [
                    'access_token' => $accessToken,
                ],
            ]);

            return UserProfileDTO::fromArray($response->toArray());
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to get user profile: ' . $e->getMessage());
        }
    }

    public function getUserPlaylists(string $accessToken, int $limit = 25): array
    {
        try {
            $response = $this->deezerApiClient->request('GET', '/user/me/playlists', [
                'query' => [
                    'access_token' => $accessToken,
                    'limit' => $limit,
                ],
            ]);

            return $response->toArray();
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to get user playlists: ' . $e->getMessage());
        }
    }

    public function searchMusic(string $query, int $limit = 25): array
    {
        try {
            $response = $this->deezerApiClient->request('GET', '/search', [
                'query' => [
                    'q' => $query,
                    'limit' => $limit,
                ],
            ]);

            $data = $response->toArray();
            return array_map(fn($item) => TrackDTO::fromArray($item), $data['data'] ?? []);
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to search music: ' . $e->getMessage());
        }
    }

    public function getTrack(string $trackId): TrackDTO
    {
        try {
            $response = $this->deezerApiClient->request('GET', '/track/' . $trackId);

            return TrackDTO::fromArray($response->toArray());
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to get track: ' . $e->getMessage());
        }
    }

    public function getAlbum(string $albumId): array
    {
        try {
            $response = $this->deezerApiClient->request('GET', '/album/' . $albumId);

            return $response->toArray();
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to get album: ' . $e->getMessage());
        }
    }

    public function getArtist(string $artistId): array
    {
        try {
            $response = $this->deezerApiClient->request('GET', '/artist/' . $artistId);

            return $response->toArray();
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to get artist: ' . $e->getMessage());
        }
    }
}