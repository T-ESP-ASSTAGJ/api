<?php

declare(strict_types=1);

namespace App\Service\SoundCloud;

use App\Entity\Token;
use App\Entity\User;
use App\Repository\TokenRepository;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SoundCloudAuthService
{
    public function __construct(
        private readonly HttpClientInterface $soundCloudApiClient,
        private readonly HttpClientInterface $soundCloudAuthClient,
        private readonly TokenRepository $tokenRepository,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $redirectUri,
        private readonly string $authUrl,
        private readonly string $tokenUrl
    ) {}

    public function getAuthorizationUrl(array $scopes = []): string
    {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => implode(' ', $scopes),
        ];

        return $this->authUrl . '?' . http_build_query($params);
    }

    public function exchangeCodeForToken(string $code, User $user): Token
    {
        try {
            $response = $this->soundCloudAuthClient->request('POST', $this->tokenUrl, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'redirect_uri' => $this->redirectUri,
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                ],
            ]);

            $tokenData = $response->toArray();

            if (!isset($tokenData['access_token'])) {
                throw new \Exception('No access token received from SoundCloud');
            }

            // Remove existing token for this user and platform
            $existingToken = $this->tokenRepository->findByUserAndPlatform($user, Token::PLATFORM_SOUNDCLOUD);
            if ($existingToken) {
                $this->tokenRepository->remove($existingToken, true);
            }

            // Create new token
            $token = new Token();
            $token->setUser($user)
                ->setPlatform(Token::PLATFORM_SOUNDCLOUD)
                ->setAccessToken($tokenData['access_token'])
                ->setRefreshToken($tokenData['refresh_token'] ?? null)
                ->setExpiresAt(new \DateTime('+' . ($tokenData['expires_in'] ?? 3600) . ' seconds'))
                ->setScopes(explode(' ', $tokenData['scope'] ?? ''));

            // Get user profile to set platform user ID
            $userProfile = $this->getUserProfile($tokenData['access_token']);
            $token->setPlatformUserId((string)$userProfile['id']);

            $this->tokenRepository->save($token, true);

            return $token;
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to exchange code for token: ' . $e->getMessage());
        }
    }

    public function refreshToken(Token $token): Token
    {
        if ($token->getPlatform() !== Token::PLATFORM_SOUNDCLOUD) {
            throw new \InvalidArgumentException('Token is not for SoundCloud platform');
        }

        if (!$token->getRefreshToken()) {
            throw new \Exception('No refresh token available');
        }

        try {
            $response = $this->soundCloudAuthClient->request('POST', $this->tokenUrl, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
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

            $this->tokenRepository->save($token, true);

            return $token;
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to refresh token: ' . $e->getMessage());
        }
    }

    private function getUserProfile(string $accessToken): array
    {
        try {
            $response = $this->soundCloudApiClient->request('GET', '/me', [
                'query' => [
                    'oauth_token' => $accessToken,
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