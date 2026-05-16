<?php

declare(strict_types=1);

namespace App\Tests\Service\Spotify;

use App\ApiResource\Spotify\AlbumDTO;
use App\ApiResource\Spotify\ArtistDTO;
use App\ApiResource\Spotify\PlaylistDTO;
use App\ApiResource\Spotify\TrackDTO;
use App\Service\Spotify\SpotifyService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SpotifyServiceTest extends TestCase
{
    private HttpClientInterface&\PHPUnit\Framework\MockObject\MockObject $httpClient;

    private ObjectMapperInterface&\PHPUnit\Framework\MockObject\MockObject $objectMapper;

    private SpotifyService $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->objectMapper = $this->createMock(ObjectMapperInterface::class);
        $this->service = new SpotifyService($this->httpClient, $this->objectMapper);
    }

    public function testGetUserPlaylistsReturnsMappedDTOs(): void
    {
        $playlistData = ['id' => 'p1', 'name' => 'Playlist 1'];
        $this->httpClient->method('request')->willReturn($this->mockResponse(['items' => [$playlistData]]));

        $playlistDTO = new PlaylistDTO('p1', 'Playlist 1', null, true, 5, null, 'https://spotify.com/p1', []);
        $this->objectMapper->method('map')->willReturn($playlistDTO);

        $result = $this->service->getUserPlaylists('token');

        $this->assertCount(1, $result);
        $this->assertSame('p1', $result[0]->id);
    }

    public function testGetUserPlaylistsReturnsEmptyWhenNoItems(): void
    {
        $this->httpClient->method('request')->willReturn($this->mockResponse(['items' => []]));

        $result = $this->service->getUserPlaylists('token');

        $this->assertSame([], $result);
    }

    public function testGetUserPlaylistsThrowsOnHttpError(): void
    {
        $this->httpClient->method('request')->willThrowException(new \RuntimeException('Timeout'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to get user playlists');

        $this->service->getUserPlaylists('token');
    }

    public function testSearchMusicReturnsMappedTracksForTrackType(): void
    {
        $trackData = ['id' => 't1', 'name' => 'Song'];
        $this->httpClient->method('request')->willReturn($this->mockResponse(['tracks' => ['items' => [$trackData]]]));

        $trackDTO = $this->makeTrackDTO();
        $this->objectMapper->method('map')->willReturn($trackDTO);

        $result = $this->service->searchMusic('token', 'song');

        $this->assertCount(1, $result);
        $this->assertInstanceOf(TrackDTO::class, $result[0]);
    }

    public function testSearchMusicReturnsRawDataForNonTrackType(): void
    {
        $responseData = ['artists' => ['items' => [['id' => 'ar1', 'name' => 'Artist']]]];
        $this->httpClient->method('request')->willReturn($this->mockResponse($responseData));

        $result = $this->service->searchMusic('token', 'artist', 'artist');

        $this->assertSame($responseData, $result);
    }

    public function testSearchMusicThrowsOnHttpError(): void
    {
        $this->httpClient->method('request')->willThrowException(new \RuntimeException('Error'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to search music');

        $this->service->searchMusic('token', 'query');
    }

    public function testGetTrackReturnsMappedDTO(): void
    {
        $trackData = ['id' => 't1', 'name' => 'Song'];
        $this->httpClient->method('request')->willReturn($this->mockResponse($trackData));

        $trackDTO = $this->makeTrackDTO();
        $this->objectMapper->method('map')->willReturn($trackDTO);

        $result = $this->service->getTrack('token', 't1');

        $this->assertInstanceOf(TrackDTO::class, $result);
        $this->assertSame('t1', $result->id);
    }

    public function testGetTrackThrowsOnHttpError(): void
    {
        $this->httpClient->method('request')->willThrowException(new \RuntimeException('Error'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to get track');

        $this->service->getTrack('token', 't1');
    }

    public function testGetAlbumReturnsMappedDTO(): void
    {
        $albumData = ['id' => 'a1'];
        $this->httpClient->method('request')->willReturn($this->mockResponse($albumData));

        $albumDTO = new AlbumDTO('a1', 'Album', [], 'album', 10, '2023', null, 'https://spotify.com/a1', [], 80);
        $this->objectMapper->method('map')->willReturn($albumDTO);

        $result = $this->service->getAlbum('token', 'a1');

        $this->assertInstanceOf(AlbumDTO::class, $result);
    }

    public function testGetAlbumThrowsOnHttpError(): void
    {
        $this->httpClient->method('request')->willThrowException(new \RuntimeException('Error'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to get album');

        $this->service->getAlbum('token', 'a1');
    }

    public function testGetArtistReturnsMappedDTO(): void
    {
        $artistData = ['id' => 'ar1'];
        $this->httpClient->method('request')->willReturn($this->mockResponse($artistData));

        $artistDTO = new ArtistDTO('ar1', 'Artist', [], null, 'https://spotify.com/ar1', 5000, 75);
        $this->objectMapper->method('map')->willReturn($artistDTO);

        $result = $this->service->getArtist('token', 'ar1');

        $this->assertInstanceOf(ArtistDTO::class, $result);
    }

    public function testGetArtistThrowsOnHttpError(): void
    {
        $this->httpClient->method('request')->willThrowException(new \RuntimeException('Error'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to get artist');

        $this->service->getArtist('token', 'ar1');
    }

    private function makeTrackDTO(): TrackDTO
    {
        return new TrackDTO('t1', 'Track', ['Artist'], null, null, 180000, 70, null, null, 'https://open.spotify.com/track/t1');
    }

    /**
     * @param array<mixed> $data
     */
    private function mockResponse(array $data): ResponseInterface&\PHPUnit\Framework\MockObject\MockObject
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn($data);

        return $response;
    }
}
