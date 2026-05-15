<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\PlatformAuth\Spotify;

use App\ApiResource\PlatformAuth\Spotify\AuthSpotifyCallbackOutput;
use App\Entity\Token;
use PHPUnit\Framework\TestCase;

class AuthSpotifyCallbackOutputTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $output = new AuthSpotifyCallbackOutput();

        $this->assertSame(Token::PLATFORM_SPOTIFY, $output->platform);
        $this->assertNull($output->expires_at);
        $this->assertNull($output->message);
        $this->assertNull($output->error);
        $this->assertNull($output->details);
        $this->assertNull($output->token);
        $this->assertNull($output->refresh_token);
    }

    public function testSuccessfulCallbackFields(): void
    {
        $output = new AuthSpotifyCallbackOutput();
        $output->success = true;
        $output->token = 'access_token_abc';
        $output->refresh_token = 'refresh_token_xyz';
        $output->expires_at = '2026-05-15T12:00:00+00:00';

        $this->assertTrue($output->success);
        $this->assertSame('access_token_abc', $output->token);
        $this->assertSame('refresh_token_xyz', $output->refresh_token);
        $this->assertSame('2026-05-15T12:00:00+00:00', $output->expires_at);
    }

    public function testFailedCallbackFields(): void
    {
        $output = new AuthSpotifyCallbackOutput();
        $output->success = false;
        $output->error = 'access_denied';
        $output->details = 'User denied access';

        $this->assertFalse($output->success);
        $this->assertSame('access_denied', $output->error);
        $this->assertSame('User denied access', $output->details);
    }
}
