<?php

declare(strict_types=1);

namespace App\Tests\Service\Spotify;

use App\Entity\Token;
use App\Entity\User;
use App\Repository\TokenRepository;
use App\Service\Spotify\SpotifyAuthService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SpotifyAuthServiceTest extends TestCase
{
    private HttpClientInterface&\PHPUnit\Framework\MockObject\MockObject $spotifyApiClient;

    private HttpClientInterface&\PHPUnit\Framework\MockObject\MockObject $spotifyAuthClient;

    private TokenRepository&\PHPUnit\Framework\MockObject\MockObject $tokenRepository;

    private EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject $em;

    private SpotifyAuthService $service;

    protected function setUp(): void
    {
        $this->spotifyApiClient = $this->createMock(HttpClientInterface::class);
        $this->spotifyAuthClient = $this->createMock(HttpClientInterface::class);
        $this->tokenRepository = $this->createMock(TokenRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);

        $this->service = new SpotifyAuthService(
            $this->spotifyApiClient,
            $this->spotifyAuthClient,
            $this->tokenRepository,
            $this->em,
            'client-id',
            'client-secret',
            'https://example.com/callback',
        );
    }

    public function testGetRedirectUriContainsRequiredParams(): void
    {
        $uri = $this->service->getRedirectUri('my-state');

        $this->assertStringStartsWith(SpotifyAuthService::SPOTIFY_AUTH_URL, $uri);
        $this->assertStringContainsString('client_id=client-id', $uri);
        $this->assertStringContainsString('response_type=code', $uri);
        $this->assertStringContainsString('state=my-state', $uri);
        $this->assertStringContainsString('redirect_uri=', $uri);
    }

    public function testExchangeCodeForTokenCreatesNewToken(): void
    {
        $user = new User();

        $profileResponse = $this->createMock(ResponseInterface::class);
        $profileResponse->method('toArray')->willReturn(['id' => 'spotify-user-id']);

        $tokenResponse = $this->createMock(ResponseInterface::class);
        $tokenResponse->method('toArray')->willReturn([
            'access_token' => 'new-access-token',
            'refresh_token' => 'new-refresh-token',
            'expires_in' => 3600,
            'scope' => 'user-read-email',
        ]);

        $this->spotifyAuthClient->method('request')->willReturn($tokenResponse);
        $this->spotifyApiClient->method('request')->willReturn($profileResponse);
        $this->tokenRepository->method('findByUserAndPlatform')->willReturn(null);

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $token = $this->service->exchangeCodeForToken('auth-code', $user);

        $this->assertInstanceOf(Token::class, $token);
        $this->assertSame('new-access-token', $token->getAccessToken());
        $this->assertSame(Token::PLATFORM_SPOTIFY, $token->getPlatform());
    }

    public function testExchangeCodeForTokenRemovesExistingToken(): void
    {
        $user = new User();
        $existingToken = new Token();

        $profileResponse = $this->createMock(ResponseInterface::class);
        $profileResponse->method('toArray')->willReturn(['id' => 'spotify-user-id']);

        $tokenResponse = $this->createMock(ResponseInterface::class);
        $tokenResponse->method('toArray')->willReturn([
            'access_token' => 'new-token',
            'expires_in' => 3600,
            'scope' => '',
        ]);

        $this->spotifyAuthClient->method('request')->willReturn($tokenResponse);
        $this->spotifyApiClient->method('request')->willReturn($profileResponse);
        $this->tokenRepository->method('findByUserAndPlatform')->willReturn($existingToken);

        $this->em->expects($this->once())->method('remove')->with($existingToken);
        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $this->service->exchangeCodeForToken('auth-code', $user);
    }

    public function testExchangeCodeForTokenThrowsWhenNoAccessToken(): void
    {
        $user = new User();

        $tokenResponse = $this->createMock(ResponseInterface::class);
        $tokenResponse->method('toArray')->willReturn([]);

        $this->spotifyAuthClient->method('request')->willReturn($tokenResponse);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to exchange code for token');

        $this->service->exchangeCodeForToken('bad-code', $user);
    }

    public function testRefreshTokenThrowsForNonSpotifyPlatform(): void
    {
        $token = new Token();
        $token->setPlatform('deezer');

        $this->expectException(\InvalidArgumentException::class);
        $this->service->refreshToken($token);
    }

    public function testRefreshTokenThrowsWhenNoRefreshToken(): void
    {
        $token = new Token();
        $token->setPlatform(Token::PLATFORM_SPOTIFY);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No refresh token available');

        $this->service->refreshToken($token);
    }

    public function testRefreshTokenUpdatesAccessToken(): void
    {
        $token = new Token();
        $token->setPlatform(Token::PLATFORM_SPOTIFY);
        $token->setRefreshToken('old-refresh');
        $token->setExpiresAt(new \DateTime('+1 hour'));
        $token->setAccessToken('old-access');
        $token->setScopes([]);

        $user = new User();
        $token->setUser($user);
        $token->setPlatformUserId('u1');

        $refreshResponse = $this->createMock(ResponseInterface::class);
        $refreshResponse->method('toArray')->willReturn([
            'access_token' => 'refreshed-token',
            'expires_in' => 3600,
            'refresh_token' => 'new-refresh',
            'scope' => 'user-read-email',
        ]);

        $this->spotifyAuthClient->method('request')->willReturn($refreshResponse);
        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $result = $this->service->refreshToken($token);

        $this->assertSame('refreshed-token', $result->getAccessToken());
        $this->assertSame('new-refresh', $result->getRefreshToken());
    }

    public function testRefreshTokenThrowsWhenHttpClientFails(): void
    {
        $token = new Token();
        $token->setPlatform(Token::PLATFORM_SPOTIFY);
        $token->setRefreshToken('old-refresh');

        $this->spotifyAuthClient->method('request')
            ->willThrowException(new \RuntimeException('Connection refused'))
        ;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to refresh token');

        $this->service->refreshToken($token);
    }

    public function testValidateTokenReturnsTrueWhenProfileSucceeds(): void
    {
        $profileResponse = $this->createMock(ResponseInterface::class);
        $profileResponse->method('toArray')->willReturn(['id' => 'user-id']);

        $this->spotifyApiClient->method('request')->willReturn($profileResponse);

        $this->assertTrue($this->service->validateToken('valid-token'));
    }

    public function testValidateTokenReturnsFalseWhenProfileThrows(): void
    {
        $this->spotifyApiClient->method('request')
            ->willThrowException(new \RuntimeException('Network error'))
        ;

        $this->assertFalse($this->service->validateToken('bad-token'));
    }
}
