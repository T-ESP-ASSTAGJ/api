<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\ApiResource\Spotify\TrackDTO;
use App\Entity\Token;
use App\Entity\User;
use App\Repository\TokenRepository;
use App\Service\MusicMetadataService;
use App\Service\Spotify\SpotifyService;
use PHPUnit\Framework\TestCase;

class MusicMetadataServiceTest extends TestCase
{
    private SpotifyService&\PHPUnit\Framework\MockObject\MockObject $spotifyService;

    private TokenRepository&\PHPUnit\Framework\MockObject\MockObject $tokenRepository;

    private MusicMetadataService $service;

    /**
     * @var array{platform: string, track_id: string, fallback_ids: array<string, string>}
     */
    private array $track = [
        'platform' => Token::PLATFORM_SPOTIFY,
        'track_id' => 'track-123',
        'fallback_ids' => [],
    ];

    protected function setUp(): void
    {
        $this->spotifyService = $this->createMock(SpotifyService::class);
        $this->tokenRepository = $this->createMock(TokenRepository::class);
        $this->service = new MusicMetadataService($this->spotifyService, $this->tokenRepository);
    }

    public function testGetTrackMetadataReturnsUnavailableWhenNoAccessToken(): void
    {
        $this->tokenRepository->method('findOneBy')->willReturn(null);

        $result = $this->service->getTrackMetadata($this->track);

        $this->assertSame('unavailable', $result['availability']);
        $this->assertSame('Morceau indisponible', $result['title']);
    }

    public function testGetTrackMetadataReturnsUnavailableWhenTokenExpired(): void
    {
        $token = $this->createMock(Token::class);
        $token->method('isExpired')->willReturn(true);
        $this->tokenRepository->method('findOneBy')->willReturn($token);

        $result = $this->service->getTrackMetadata($this->track);

        $this->assertSame('unavailable', $result['availability']);
    }

    public function testGetTrackMetadataReturnsAvailableForSpotify(): void
    {
        $token = $this->createMock(Token::class);
        $token->method('isExpired')->willReturn(false);
        $token->method('getAccessToken')->willReturn('access-token');
        $this->tokenRepository->method('findOneBy')->willReturn($token);

        $trackDTO = new TrackDTO(
            id: 'track-123',
            name: 'Song Name',
            artists: ['Artist One'],
            albumId: 'album-1',
            albumName: 'Album',
            durationMs: 200000,
            popularity: 80,
            previewUrl: 'https://preview.url',
            imageUrl: 'https://image.url',
            externalUrl: 'https://open.spotify.com/track/track-123',
        );
        $this->spotifyService->method('getTrack')->with('access-token', 'track-123')->willReturn($trackDTO);

        $result = $this->service->getTrackMetadata($this->track);

        $this->assertSame('available', $result['availability']);
        $this->assertSame('Song Name', $result['title']);
        $this->assertSame('Artist One', $result['artist']);
        $this->assertSame('https://image.url', $result['album_cover']);
        $this->assertSame('https://preview.url', $result['preview_url']);
    }

    public function testGetTrackMetadataReturnsUnavailableWhenSpotifyThrows(): void
    {
        $token = $this->createMock(Token::class);
        $token->method('isExpired')->willReturn(false);
        $token->method('getAccessToken')->willReturn('access-token');
        $this->tokenRepository->method('findOneBy')->willReturn($token);

        $this->spotifyService->method('getTrack')->willThrowException(new \RuntimeException('Spotify error'));

        $result = $this->service->getTrackMetadata($this->track);

        $this->assertSame('unavailable', $result['availability']);
    }

    public function testGetTrackMetadataUsesRecipientSpotifyTokenWhenAvailable(): void
    {
        $recipient = new User();

        $recipientToken = $this->createMock(Token::class);
        $recipientToken->method('isExpired')->willReturn(false);

        $platformToken = $this->createMock(Token::class);
        $platformToken->method('isExpired')->willReturn(false);
        $platformToken->method('getAccessToken')->willReturn('platform-token');

        $trackDTO = new TrackDTO('t', 'T', [], null, null, 0, 0, null, null, 'url');

        $this->tokenRepository->method('findOneBy')->willReturnCallback(function (array $criteria) use ($recipientToken, $platformToken) {
            if (isset($criteria['user'])) {
                return $recipientToken;
            }

            return $platformToken;
        });

        $this->spotifyService->method('getTrack')->willReturn($trackDTO);

        $result = $this->service->getTrackMetadata($this->track, $recipient);

        $this->assertSame('available', $result['availability']);
    }

    public function testGetTrackMetadataFallsBackToOriginalPlatformWhenRecipientTokenExpired(): void
    {
        $recipient = new User();

        $expiredToken = $this->createMock(Token::class);
        $expiredToken->method('isExpired')->willReturn(true);

        $this->tokenRepository->method('findOneBy')->willReturnCallback(function (array $criteria) use ($expiredToken) {
            if (isset($criteria['user'])) {
                return $expiredToken;
            }

            return null; // no platform token either
        });

        $result = $this->service->getTrackMetadata($this->track, $recipient);

        $this->assertSame('unavailable', $result['availability']);
    }

    public function testGetTrackMetadataReturnsUnavailableForUnsupportedPlatform(): void
    {
        // Platform is 'deezer', has a valid token, but fetchMetadataFromPlatform hits the default branch
        $track = [
            'platform' => 'deezer',
            'track_id' => 'deezer-track-123',
            'fallback_ids' => [],
        ];

        $token = $this->createMock(Token::class);
        $token->method('isExpired')->willReturn(false);
        $token->method('getAccessToken')->willReturn('deezer-access-token');
        $this->tokenRepository->method('findOneBy')->willReturn($token);

        $result = $this->service->getTrackMetadata($track);

        $this->assertSame('unavailable', $result['availability']);
    }

    public function testGetTrackMetadataReturnsUnavailableWhenFallbackIdMissing(): void
    {
        // Track is on Deezer; recipient has a Spotify token → target becomes Spotify,
        // but the track has no fallback_ids for Spotify → trackId is null → unavailable.
        $track = [
            'platform' => 'deezer',
            'track_id' => 'deezer-track',
            'fallback_ids' => [],
        ];

        $recipient = new User();

        $recipientToken = $this->createMock(Token::class);
        $recipientToken->method('isExpired')->willReturn(false);

        $this->tokenRepository->method('findOneBy')->willReturnCallback(function (array $criteria) use ($recipientToken) {
            if (isset($criteria['user'])) {
                return $recipientToken;
            }

            return null;
        });

        $result = $this->service->getTrackMetadata($track, $recipient);

        $this->assertSame('unavailable', $result['availability']);
    }
}
