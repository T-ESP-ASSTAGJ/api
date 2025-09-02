<?php

declare(strict_types=1);

namespace App\Service\SoundCloud;

use App\DTO\SoundCloud\UserProfileDTO;
use App\DTO\SoundCloud\TrackDTO;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SoundCloudService
{
    public function __construct(
        private readonly HttpClientInterface $soundCloudApiClient
    ) {}

    public function getUserProfile(string $accessToken): UserProfileDTO
    {
        try {
            $response = $this->soundCloudApiClient->request('GET', '/me', [
                'query' => [
                    'oauth_token' => $accessToken,
                ],
            ]);

            return UserProfileDTO::fromArray($response->toArray());
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to get user profile: ' . $e->getMessage());
        }
    }

    public function getUserPlaylists(string $accessToken, int $limit = 50): array
    {
        try {
            $response = $this->soundCloudApiClient->request('GET', '/me/playlists', [
                'query' => [
                    'oauth_token' => $accessToken,
                    'limit' => $limit,
                ],
            ]);

            return $response->toArray();
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to get user playlists: ' . $e->getMessage());
        }
    }

    public function searchTracks(string $accessToken, string $query, int $limit = 50): array
    {
        try {
            $response = $this->soundCloudApiClient->request('GET', '/tracks', [
                'query' => [
                    'oauth_token' => $accessToken,
                    'q' => $query,
                    'limit' => $limit,
                ],
            ]);

            $data = $response->toArray();
            return array_map(fn($item) => TrackDTO::fromArray($item), $data);
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to search tracks: ' . $e->getMessage());
        }
    }

    public function getTrack(string $accessToken, string $trackId): TrackDTO
    {
        try {
            $response = $this->soundCloudApiClient->request('GET', '/tracks/' . $trackId, [
                'query' => [
                    'oauth_token' => $accessToken,
                ],
            ]);

            return TrackDTO::fromArray($response->toArray());
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to get track: ' . $e->getMessage());
        }
    }

    public function getUserTracks(string $accessToken, int $limit = 50): array
    {
        try {
            $response = $this->soundCloudApiClient->request('GET', '/me/tracks', [
                'query' => [
                    'oauth_token' => $accessToken,
                    'limit' => $limit,
                ],
            ]);

            $data = $response->toArray();
            return array_map(fn($item) => TrackDTO::fromArray($item), $data);
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to get user tracks: ' . $e->getMessage());
        }
    }

    public function getUserFollowings(string $accessToken, int $limit = 50): array
    {
        try {
            $response = $this->soundCloudApiClient->request('GET', '/me/followings', [
                'query' => [
                    'oauth_token' => $accessToken,
                    'limit' => $limit,
                ],
            ]);

            return $response->toArray();
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to get user followings: ' . $e->getMessage());
        }
    }
}