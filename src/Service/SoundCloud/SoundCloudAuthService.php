<?php

declare(strict_types=1);

namespace App\Service\SoundCloud;

use App\Entity\Token;
use App\Entity\User;
use App\Repository\TokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class SoundCloudAuthService
{
    public const SOUNDCLOUD_TOKEN_URL = 'https://api.soundcloud.com/oauth2/token';
    public const SOUNDCLOUD_AUTH_URL = 'https://soundcloud.com/connect';

    public function __construct(
        private HttpClientInterface $soundcloudApiClient,
        private HttpClientInterface $soundcloudAuthClient,
        private TokenRepository $tokenRepository,
        private EntityManagerInterface $entityManager,
        #[Autowire(env: 'SOUNDCLOUD_CLIENT_ID')]
        private string $clientId,
        #[Autowire(env: 'SOUNDCLOUD_CLIENT_SECRET')]
        private string $clientSecret,
        #[Autowire(env: 'SOUNDCLOUD_REDIRECT_URI')]
        private string $redirectUri,
    ) {
    }

    public function getRedirectUri(string $state): string
    {
        return self::SOUNDCLOUD_AUTH_URL.
            '?response_type=code'.
            '&client_id='.$this->clientId.
            '&scope=non-expiring'.
            '&redirect_uri='.$this->redirectUri.
            '&state='.urlencode($state);
    }

    public function exchangeCodeForToken(string $code, User $user): Token
    {
        try {
            $response = $this->soundcloudAuthClient->request('POST', self::SOUNDCLOUD_TOKEN_URL, [
                'body' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => $this->redirectUri,
                ],
            ]);

            $tokenData = $response->toArray();

            if (!isset($tokenData['access_token'])) {
                throw new \RuntimeException('No access token received from SoundCloud');
            }

            $existingToken = $this->tokenRepository->findByUserAndPlatform($user, Token::PLATFORM_SOUNDCLOUD);
            if ($existingToken) {
                $this->entityManager->remove($existingToken);
            }

            $token = new Token();
            $token->setUser($user)
                ->setPlatform(Token::PLATFORM_SOUNDCLOUD)
                ->setAccessToken($tokenData['access_token'])
                ->setRefreshToken($tokenData['refresh_token'] ?? null)
                ->setExpiresAt(new \DateTime('+'.($tokenData['expires_in'] ?? 3600).' seconds'))
                ->setScopes(isset($tokenData['scope']) ? explode(' ', $tokenData['scope']) : []);

            $userProfile = $this->getUserProfile($tokenData['access_token']);
            $token->setPlatformUserId((string) $userProfile['id']);

            $this->entityManager->persist($token);
            $this->entityManager->flush();

            return $token;
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to exchange code for token: '.$e->getMessage());
        }
    }

    public function refreshToken(Token $token): Token
    {
        if (Token::PLATFORM_SOUNDCLOUD !== $token->getPlatform()) {
            throw new \InvalidArgumentException('Token is not for SoundCloud platform');
        }

        if (!$token->getRefreshToken()) {
            throw new \RuntimeException('No refresh token available');
        }

        try {
            $response = $this->soundcloudAuthClient->request('POST', self::SOUNDCLOUD_TOKEN_URL, [
                'body' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $token->getRefreshToken(),
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ],
            ]);

            $tokenData = $response->toArray();

            $token->setAccessToken($tokenData['access_token'])
                ->setExpiresAt(new \DateTime('+'.($tokenData['expires_in'] ?? 3600).' seconds'));

            if (isset($tokenData['refresh_token'])) {
                $token->setRefreshToken($tokenData['refresh_token']);
            }

            if (isset($tokenData['scope'])) {
                $token->setScopes(explode(' ', $tokenData['scope']));
            }

            $this->entityManager->persist($token);
            $this->entityManager->flush();

            return $token;
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to refresh token: '.$e->getMessage());
        }
    }

    /** @return array<string> */
    private function getUserProfile(string $accessToken): array
    {
        try {
            $response = $this->soundcloudApiClient->request('GET', 'me', [
                'headers' => [
                    'Authorization' => 'OAuth '.$accessToken,
                ],
            ]);

            return $response->toArray();
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to get user profile: '.$e->getMessage());
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