<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Token;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $token = new Token();
        $user = new User();

        $this->assertNull($token->getId());

        $result = $token->setUser($user);
        $this->assertSame($token, $result);
        $this->assertSame($user, $token->getUser());

        $result = $token->setPlatform(Token::PLATFORM_SPOTIFY);
        $this->assertSame($token, $result);
        $this->assertSame(Token::PLATFORM_SPOTIFY, $token->getPlatform());

        $result = $token->setAccessToken('access_token_123');
        $this->assertSame($token, $result);
        $this->assertSame('access_token_123', $token->getAccessToken());

        $result = $token->setRefreshToken('refresh_token_456');
        $this->assertSame($token, $result);
        $this->assertSame('refresh_token_456', $token->getRefreshToken());

        $expiresAt = new \DateTime('2025-12-31 23:59:59');
        $result = $token->setExpiresAt($expiresAt);
        $this->assertSame($token, $result);
        $this->assertSame($expiresAt, $token->getExpiresAt());

        $result = $token->setPlatformUserId('spotify_user_123');
        $this->assertSame($token, $result);
        $this->assertSame('spotify_user_123', $token->getPlatformUserId());

        $scopes = ['user-read-email', 'user-read-private', 'playlist-read-private'];
        $result = $token->setScopes($scopes);
        $this->assertSame($token, $result);
        $this->assertSame($scopes, $token->getScopes());
    }

    public function testDefaultValues(): void
    {
        $token = new Token();

        $this->assertNull($token->getRefreshToken());
        $this->assertSame([], $token->getScopes());
    }

    public function testIsExpiredReturnsTrueWhenExpired(): void
    {
        $token = new Token();
        $pastDate = new \DateTime('-1 day');
        $token->setExpiresAt($pastDate);

        $this->assertTrue($token->isExpired());
    }

    public function testIsExpiredReturnsFalseWhenNotExpired(): void
    {
        $token = new Token();
        $futureDate = new \DateTime('+1 day');
        $token->setExpiresAt($futureDate);

        $this->assertFalse($token->isExpired());
    }

    public function testPlatformConstants(): void
    {
        $this->assertSame('spotify', Token::PLATFORM_SPOTIFY);
        $this->assertSame('deezer', Token::PLATFORM_DEEZER);
        $this->assertSame('soundcloud', Token::PLATFORM_SOUNDCLOUD);
        $this->assertSame('apple_music', Token::PLATFORM_APPLE_MUSIC);
    }
}
