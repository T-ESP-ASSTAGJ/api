<?php

declare(strict_types=1);

namespace App\Service\AppleMusic;

use App\DTO\AppleMusic\SongDTO;
use App\DTO\AppleMusic\PlaylistDTO;
use App\DTO\AppleMusic\AlbumDTO;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AppleMusicService
{
    public function __construct(
        private readonly HttpClientInterface $appleMusicApiClient,
        private readonly string $developerToken
    ) {}

    public function getUserLibraryPlaylists(string $musicUserToken, int $limit = 25): array
    {
        try {
            $response = $this->appleMusicApiClient->request('GET', '/me/library/playlists', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->developerToken,
                    'Music-User-Token' => $musicUserToken,
                ],
                'query' => [
                    'limit' => $limit,
                ],
            ]);

            $data = $response->toArray();
            return array_map(fn($item) => PlaylistDTO::fromArray($item), $data['data'] ?? []);
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to get user library playlists: ' . $e->getMessage());
        }
    }

    public function getUserLibrarySongs(string $musicUserToken, int $limit = 25): array
    {
        try {
            $response = $this->appleMusicApiClient->request('GET', '/me/library/songs', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->developerToken,
                    'Music-User-Token' => $musicUserToken,
                ],
                'query' => [
                    'limit' => $limit,
                ],
            ]);

            $data = $response->toArray();
            return array_map(fn($item) => SongDTO::fromArray($item), $data['data'] ?? []);
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to get user library songs: ' . $e->getMessage());
        }
    }

    public function searchCatalog(string $musicUserToken, string $query, array $types = ['songs'], int $limit = 25): array
    {
        try {
            $response = $this->appleMusicApiClient->request('GET', '/catalog/us/search', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->developerToken,
                    'Music-User-Token' => $musicUserToken,
                ],
                'query' => [
                    'term' => $query,
                    'types' => implode(',', $types),
                    'limit' => $limit,
                ],
            ]);

            $data = $response->toArray();
            $results = [];

            if (in_array('songs', $types) && isset($data['results']['songs']['data'])) {
                $results['songs'] = array_map(fn($item) => SongDTO::fromArray($item), $data['results']['songs']['data']);
            }

            if (in_array('albums', $types) && isset($data['results']['albums']['data'])) {
                $results['albums'] = array_map(fn($item) => AlbumDTO::fromArray($item), $data['results']['albums']['data']);
            }

            return $results;
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to search catalog: ' . $e->getMessage());
        }
    }

    public function getSong(string $musicUserToken, string $songId): SongDTO
    {
        try {
            $response = $this->appleMusicApiClient->request('GET', '/catalog/us/songs/' . $songId, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->developerToken,
                    'Music-User-Token' => $musicUserToken,
                ],
            ]);

            $data = $response->toArray();
            return SongDTO::fromArray($data['data'][0] ?? []);
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to get song: ' . $e->getMessage());
        }
    }

    public function getAlbum(string $musicUserToken, string $albumId): AlbumDTO
    {
        try {
            $response = $this->appleMusicApiClient->request('GET', '/catalog/us/albums/' . $albumId, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->developerToken,
                    'Music-User-Token' => $musicUserToken,
                ],
            ]);

            $data = $response->toArray();
            return AlbumDTO::fromArray($data['data'][0] ?? []);
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to get album: ' . $e->getMessage());
        }
    }

    public function getArtist(string $musicUserToken, string $artistId): array
    {
        try {
            $response = $this->appleMusicApiClient->request('GET', '/catalog/us/artists/' . $artistId, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->developerToken,
                    'Music-User-Token' => $musicUserToken,
                ],
            ]);

            return $response->toArray();
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to get artist: ' . $e->getMessage());
        }
    }

    public function getRecommendations(string $musicUserToken, int $limit = 10): array
    {
        try {
            $response = $this->appleMusicApiClient->request('GET', '/me/recommendations', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->developerToken,
                    'Music-User-Token' => $musicUserToken,
                ],
                'query' => [
                    'limit' => $limit,
                ],
            ]);

            return $response->toArray();
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to get recommendations: ' . $e->getMessage());
        }
    }
}