<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\PlatformAuth\Spotify;

use App\ApiResource\PlatformAuth\Spotify\AuthSpotifyOutput;
use PHPUnit\Framework\TestCase;

class AuthSpotifyOutputTest extends TestCase
{
    public function testPublicProperties(): void
    {
        $output = new AuthSpotifyOutput();
        $output->authorization_url = 'https://accounts.spotify.com/authorize?client_id=abc';
        $output->platform = 'spotify';
        $output->message = 'Redirect to Spotify to authorize';

        $this->assertSame('https://accounts.spotify.com/authorize?client_id=abc', $output->authorization_url);
        $this->assertSame('spotify', $output->platform);
        $this->assertSame('Redirect to Spotify to authorize', $output->message);
    }
}
