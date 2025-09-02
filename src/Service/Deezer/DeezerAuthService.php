<?php

declare(strict_types=1);

namespace App\Service\Deezer;

use App\Entity\Token;
use App\Entity\User;
use App\Repository\TokenRepository;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DeezerAuthService
{
    public function __construct(
        private readonly HttpClientInterface $deezerApiClient,
        private readonly HttpClientInterface $deezerAuthClient,
        private readonly TokenRepository $tokenRepository,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $redirectUri,
        private readonly string $authUrl,
        private readonly string $tokenUrl
    ) {}

    public function getAuthorizationUrl(array $scopes = ['basic_access', 'email']): string
    {
        $params = [
            'app_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'perms' => implode(',', $scopes),
        ];

        return $this->authUrl . '?' . http_build_query($params);
    }

    public function exchangeCodeForToken(string $code, User $user): Token
    {
        try {
            $response = $this->deezerAuthClient->request('GET', $this->tokenUrl, [
                'query' => [
                    'app_id' => $this->clientId,
                    'secret' => $this->clientSecret,
                    'code' => $code,
                ],
            ]);

            $content = $response->getContent();
            parse_str($content, $tokenData);

            if (!isset($tokenData['access_token'])) {
                throw new \Exception('No access token received from Deezer');
            }

            // Remove existing token for this user and platform
            $existingToken = $this->tokenRepository->findByUserAndPlatform($user, Token::PLATFORM_DEEZER);
            if ($existingToken) {
                $this->tokenRepository->remove($existingToken, true);
            }

            // Create new token
            $token = new Token();
            $token->setUser($user)
                ->setPlatform(Token::PLATFORM_DEEZER)
                ->setAccessToken($tokenData['access_token'])
                ->setExpiresAt(new \DateTime('+' . ($tokenData['expires'] ?? 3600) . ' seconds'))
                ->setScopes([]);

            // Get user profile to set platform user ID
            $userProfile = $this->getUserProfile($tokenData['access_token']);
            $token->setPlatformUserId((string)$userProfile['id']);

            $this->tokenRepository->save($token, true);

            return $token;
        } catch (ClientException | ServerException $e) {
            throw new \Exception('Failed to exchange code for token: ' . $e->getMessage());
        }
    }

    private function getUserProfile(string $accessToken): array
    {
        try {
            $response = $this->deezerApiClient->request('GET', '/user/me', [
                'query' => [
                    'access_token' => $accessToken,
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