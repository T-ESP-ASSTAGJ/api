<?php

declare(strict_types=1);

namespace App\Tests\State\Spotify;

use ApiPlatform\Metadata\Post;
use App\ApiResource\PlatformAuth\Spotify\AuthSpotifyOutput;
use App\Entity\Token;
use App\Entity\User;
use App\Service\Spotify\SpotifyAuthService;
use App\State\Spotify\AuthSpotifyProcessor;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthSpotifyProcessorTest extends TestCase
{
    public function testThrowsWhenUnauthenticated(): void
    {
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn(null);

        $processor = new AuthSpotifyProcessor(
            $this->createMock(SpotifyAuthService::class),
            $this->createMock(JWTTokenManagerInterface::class),
            $security,
        );

        $this->expectException(UnauthorizedHttpException::class);
        $processor->process(null, new Post());
    }

    public function testReturnsAuthorizationUrl(): void
    {
        $user = new User();

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($user);

        $jwtManager = $this->createMock(JWTTokenManagerInterface::class);
        $jwtManager->method('create')->willReturn('state-token');

        $spotifyService = $this->createMock(SpotifyAuthService::class);
        $spotifyService->method('getRedirectUri')->with('state-token')->willReturn('https://accounts.spotify.com/authorize?...');

        $processor = new AuthSpotifyProcessor($spotifyService, $jwtManager, $security);

        $result = $processor->process(null, new Post());

        $this->assertInstanceOf(AuthSpotifyOutput::class, $result);
        $this->assertSame('https://accounts.spotify.com/authorize?...', $result->authorization_url);
        $this->assertSame(Token::PLATFORM_SPOTIFY, $result->platform);
    }
}
