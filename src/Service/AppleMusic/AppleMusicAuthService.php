<?php

declare(strict_types=1);

namespace App\Service\AppleMusic;

use App\Entity\Token;
use App\Entity\User;
use App\Repository\TokenRepository;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AppleMusicAuthService
{
    public function __construct(
        private readonly HttpClientInterface $appleMusicApiClient,
        private readonly TokenRepository $tokenRepository,
        private readonly string $teamId,
        private readonly string $keyId,
        private readonly string $privateKey
    ) {}

    public function generateDeveloperToken(): string
    {
        throw new \Exception('Apple Music developer token generation requires firebase/php-jwt library. Please provide APPLE_MUSIC_DEVELOPER_TOKEN in your environment variables.');
    }

    public function createUserToken(User $user, string $musicUserToken): Token
    {
        // Remove existing token for this user and platform
        $existingToken = $this->tokenRepository->findByUserAndPlatform($user, Token::PLATFORM_APPLE_MUSIC);
        if ($existingToken) {
            $this->tokenRepository->remove($existingToken, true);
        }

        // Create new token
        $token = new Token();
        $token->setUser($user)
            ->setPlatform(Token::PLATFORM_APPLE_MUSIC)
            ->setAccessToken($musicUserToken)
            ->setExpiresAt(new \DateTime('+1 year')) 
            ->setScopes([]);

        $token->setPlatformUserId('apple_music_user_' . $user->getId());

        $this->tokenRepository->save($token, true);

        return $token;
    }

    public function validateToken(string $accessToken): bool
    {
        try {
            $response = $this->appleMusicApiClient->request('GET', '/me/library/songs', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->generateDeveloperToken(),
                    'Music-User-Token' => $accessToken,
                ],
                'query' => [
                    'limit' => 1,
                ],
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Exception) {
            return false;
        }
    }

    public function getAuthorizationUrl(): string
    {
        throw new \Exception('Apple Music authentication requires MusicKit JS implementation on the frontend');
    }
}