<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Artist;

use App\ApiResource\Artist\ArtistSourceDto;
use PHPUnit\Framework\TestCase;

class ArtistSourceDtoTest extends TestCase
{
    public function testConstructorWithAllParameters(): void
    {
        $dto = new ArtistSourceDto('spotify', 'spotify_artist_123');

        $this->assertSame('spotify', $dto->platform);
        $this->assertSame('spotify_artist_123', $dto->platformArtistId);
    }

    public function testConstructorWithDefaultValues(): void
    {
        $dto = new ArtistSourceDto();

        $this->assertSame('', $dto->platform);
        $this->assertSame('', $dto->platformArtistId);
    }

    public function testCanSetProperties(): void
    {
        $dto = new ArtistSourceDto();
        $dto->platform = 'deezer';
        $dto->platformArtistId = 'deezer_123';

        $this->assertSame('deezer', $dto->platform);
        $this->assertSame('deezer_123', $dto->platformArtistId);
    }

    public function testSupportsAllPlatforms(): void
    {
        $dto1 = new ArtistSourceDto('spotify', 'id1');
        $this->assertSame('spotify', $dto1->platform);

        $dto2 = new ArtistSourceDto('deezer', 'id2');
        $this->assertSame('deezer', $dto2->platform);

        $dto3 = new ArtistSourceDto('soundcloud', 'id3');
        $this->assertSame('soundcloud', $dto3->platform);
    }
}
