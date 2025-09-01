<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SpotifyService
{
    private const SPOTIFY_AUTH_URL = 'https://accounts.spotify.com/authorize';
    private const SPOTIFY_TOKEN_URL = 'https://accounts.spotify.com/api/token';
    private const SPOTIFY_API_URL = 'https://api.spotify.com/v1';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $redirectUri
    ) {}

    /**
     * Génère l'URL d'autorisation Spotify
     */
    public function getAuthorizationUrl(array $scopes = ['user-read-private', 'user-read-email']): string
    {
        $state = bin2hex(random_bytes(16));

        $params = [
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'scope' => implode(' ', $scopes),
            'redirect_uri' => $this->redirectUri,
            'state' => $state,
        ];

        $url = self::SPOTIFY_AUTH_URL . '?' . http_build_query($params);

        // Debug - log des paramètres
        error_log('Spotify OAuth params: ' . json_encode($params));
        error_log('Generated URL: ' . $url);

        return $url;
    }

    /**
     * Échange le code d'autorisation contre un access token
     */
    public function exchangeCodeForToken(string $code): array
    {
        try {
            // Utilisez le format 'body' array au lieu de http_build_query
            $response = $this->httpClient->request('POST', self::SPOTIFY_TOKEN_URL, [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => $this->redirectUri,
                ],
            ]);

            $tokenData = $response->toArray();

            if (!isset($tokenData['access_token'])) {
                throw new \Exception('No access token received from Spotify');
            }

            return $tokenData;
        } catch (ClientException | ServerException $e) {
            $errorData = json_decode($e->getResponse()->getContent(false), true);
            $errorMessage = $errorData['error_description'] ?? $e->getMessage();
            throw new \Exception('Failed to exchange code for token: ' . $errorMessage);
        }
    }

    /**
     * Rafraîchit un access token expiré
     */
    public function refreshToken(string $refreshToken): array
    {
        try {
            $response = $this->httpClient->request('POST', self::SPOTIFY_TOKEN_URL, [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken,
                ],
            ]);

            return $response->toArray();
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to refresh token: ' . $e->getMessage());
        }
    }

    /**
     * Récupère les informations du profil utilisateur Spotify
     */
    public function getUserProfile(string $accessToken): array
    {
        try {
            $response = $this->httpClient->request('GET', self::SPOTIFY_API_URL . '/me', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);

            return $response->toArray();
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to get user profile: ' . $e->getMessage());
        }
    }

    /**
     * Teste la validité d'un access token
     */
    public function validateToken(string $accessToken): bool
    {
        try {
            $this->getUserProfile($accessToken);
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Récupère les playlists de l'utilisateur
     */
    public function getUserPlaylists(string $accessToken, int $limit = 20): array
    {
        try {
            $response = $this->httpClient->request('GET', self::SPOTIFY_API_URL . '/me/playlists', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
                'query' => [
                    'limit' => $limit,
                ],
            ]);

            return $response->toArray();
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to get user playlists: ' . $e->getMessage());
        }
    }

    /**
     * Recherche de musique sur Spotify
     */
    public function searchMusic(string $accessToken, string $query, string $type = 'track', int $limit = 20): array
    {
        try {
            $response = $this->httpClient->request('GET', self::SPOTIFY_API_URL . '/search', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
                'query' => [
                    'q' => $query,
                    'type' => $type,
                    'limit' => $limit,
                ],
            ]);

            return $response->toArray();
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to search music: ' . $e->getMessage());
        }
    }
}
