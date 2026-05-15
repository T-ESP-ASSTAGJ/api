<?php

declare(strict_types=1);

namespace App\Tests\State\Spotify;

use ApiPlatform\Metadata\Get;
use App\ApiResource\PlatformAuth\Spotify\AuthSpotifyCallbackOutput;
use App\Entity\Token;
use App\Entity\User;
use App\Service\Spotify\SpotifyAuthService;
use App\State\Spotify\AuthSpotifyCallbackProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthSpotifyCallbackProviderTest extends TestCase
{
    /**
     * @var SpotifyAuthService&\PHPUnit\Framework\MockObject\MockObject
     */
    private SpotifyAuthService $spotifyService;

    /**
     * @var JWTEncoderInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private JWTEncoderInterface $jwtEncoder;

    /**
     * @var EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private EntityManagerInterface $em;

    /**
     * @var RequestStack&\PHPUnit\Framework\MockObject\MockObject
     */
    private RequestStack $requestStack;

    private AuthSpotifyCallbackProvider $provider;

    protected function setUp(): void
    {
        $this->spotifyService = $this->createMock(SpotifyAuthService::class);
        $this->jwtEncoder = $this->createMock(JWTEncoderInterface::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->provider = new AuthSpotifyCallbackProvider(
            $this->spotifyService,
            $this->jwtEncoder,
            $this->em,
            $this->requestStack,
        );
    }

    public function testThrowsWhenNoMainRequest(): void
    {
        $this->requestStack->method('getMainRequest')->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->provider->provide(new Get());
    }

    public function testThrowsWhenMissingCode(): void
    {
        $this->requestStack->method('getMainRequest')->willReturn($this->makeRequest(['state' => 'token']));

        $this->expectException(BadRequestHttpException::class);
        $this->provider->provide(new Get());
    }

    public function testThrowsWhenMissingState(): void
    {
        $this->requestStack->method('getMainRequest')->willReturn($this->makeRequest(['code' => 'abc']));

        $this->expectException(BadRequestHttpException::class);
        $this->provider->provide(new Get());
    }

    public function testThrowsWhenInvalidStateToken(): void
    {
        $this->requestStack->method('getMainRequest')->willReturn($this->makeRequest(['code' => 'abc', 'state' => 'bad']));
        $this->jwtEncoder->method('decode')->willThrowException(new \Exception('Invalid'));

        $this->expectException(UnauthorizedHttpException::class);
        $this->provider->provide(new Get());
    }

    public function testThrowsWhenUserNotFound(): void
    {
        $this->requestStack->method('getMainRequest')->willReturn($this->makeRequest(['code' => 'abc', 'state' => 'valid']));
        $this->jwtEncoder->method('decode')->willReturn(['email' => 'ghost@example.com']);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn(null);
        $this->em->method('getRepository')->willReturn($repo);

        $this->expectException(UnauthorizedHttpException::class);
        $this->provider->provide(new Get());
    }

    public function testReturnsSuccessOutputOnValidCallback(): void
    {
        $user = new User();
        $this->requestStack->method('getMainRequest')->willReturn($this->makeRequest(['code' => 'code123', 'state' => 'state123']));
        $this->jwtEncoder->method('decode')->willReturn(['email' => 'user@example.com']);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn($user);
        $this->em->method('getRepository')->willReturn($repo);

        $token = $this->createMock(Token::class);
        $token->method('getExpiresAt')->willReturn(new \DateTime('+1 hour'));
        $token->method('getAccessToken')->willReturn('access-token');
        $token->method('getRefreshToken')->willReturn('refresh-token');

        $this->spotifyService->method('exchangeCodeForToken')->willReturn($token);

        $result = $this->provider->provide(new Get());

        $this->assertInstanceOf(AuthSpotifyCallbackOutput::class, $result);
        $this->assertTrue($result->success);
        $this->assertSame('access-token', $result->token);
    }

    public function testReturnsFailureOutputWhenSpotifyFails(): void
    {
        $user = new User();
        $this->requestStack->method('getMainRequest')->willReturn($this->makeRequest(['code' => 'code123', 'state' => 'state123']));
        $this->jwtEncoder->method('decode')->willReturn(['email' => 'user@example.com']);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn($user);
        $this->em->method('getRepository')->willReturn($repo);

        $this->spotifyService->method('exchangeCodeForToken')->willThrowException(new \Exception('API error'));

        $result = $this->provider->provide(new Get());

        $this->assertInstanceOf(AuthSpotifyCallbackOutput::class, $result);
        $this->assertFalse($result->success);
        $this->assertSame('API error', $result->details);
    }

    /**
     * @param array<string, mixed> $queryParams
     */
    private function makeRequest(array $queryParams): Request
    {
        return Request::create('/spotify/callback', 'GET', $queryParams);
    }
}
