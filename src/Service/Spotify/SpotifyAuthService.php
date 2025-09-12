<?php

declare(strict_types=1);

namespace App\Service\Spotify;

use App\Entity\Token;
use App\Entity\User;
use App\Repository\TokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SpotifyAuthService
{
    public function __construct(
        private readonly HttpClientInterface $spotifyApiClient,
        private readonly HttpClientInterface $spotifyAuthClient,
        private readonly TokenRepository $tokenRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $redirectUri,
        private readonly string $authUrl,
        private readonly string $tokenUrl
    ) {}

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

        return $this->authUrl . '?' . http_build_query($params);
    }

    public function exchangeCodeForToken(string $code, User $user): Token
    {
        try {
            $response = $this->spotifyAuthClient->request('POST', $this->tokenUrl, [
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

            // Remove existing token for this user and platform
            $existingToken = $this->tokenRepository->findByUserAndPlatform($user, Token::PLATFORM_SPOTIFY);
            if ($existingToken) {
                $this->entityManager->remove($existingToken);
            }

            // Create new token
            $token = new Token();
            $token->setUser($user)
                ->setPlatform(Token::PLATFORM_SPOTIFY)
                ->setAccessToken($tokenData['access_token'])
                ->setRefreshToken($tokenData['refresh_token'] ?? null)
                ->setExpiresAt(new \DateTime('+' . ($tokenData['expires_in'] ?? 3600) . ' seconds'))
                ->setScopes(explode(' ', $tokenData['scope'] ?? ''));

            // Get user profile to set platform user ID
            $userProfile = $this->getUserProfile($tokenData['access_token']);
            $token->setPlatformUserId($userProfile['id']);

            $this->entityManager->persist($token);
            $this->entityManager->flush();

            return $token;
        } catch (ClientException | ServerException $e) {
            $errorData = json_decode($e->getResponse()->getContent(false), true);
            $errorMessage = $errorData['error_description'] ?? $e->getMessage();
            throw new \Exception('Failed to exchange code for token: ' . $errorMessage);
        }
    }

    public function refreshToken(Token $token): Token
    {
        if ($token->getPlatform() !== Token::PLATFORM_SPOTIFY) {
            throw new \InvalidArgumentException('Token is not for Spotify platform');
        }

        if (!$token->getRefreshToken()) {
            throw new \Exception('No refresh token available');
        }

        try {
            $response = $this->spotifyAuthClient->request('POST', $this->tokenUrl, [
                'body' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $token->getRefreshToken(),
                ],
            ]);

            $tokenData = $response->toArray();

            $token->setAccessToken($tokenData['access_token'])
                ->setExpiresAt(new \DateTime('+' . ($tokenData['expires_in'] ?? 3600) . ' seconds'));

            if (isset($tokenData['refresh_token'])) {
                $token->setRefreshToken($tokenData['refresh_token']);
            }

            if (isset($tokenData['scope'])) {
                $token->setScopes(explode(' ', $tokenData['scope']));
            }

            $this->entityManager->persist($token);
            $this->entityManager->flush();

            return $token;
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to refresh token: ' . $e->getMessage());
        }
    }

    private function getUserProfile(string $accessToken): array
    {
        try {
            $response = $this->spotifyApiClient->request('GET', '/me', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);

            return $response->toArray();
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to get user profile: ' . $e->getMessage());
        }
    }

    public function validateToken(string $accessToken): bool
    {
        try {
            $this->getUserProfile($accessToken);
            return true;
        } catch (\Exception) {
            return false;
        }
    }
}