<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Artist;
use App\Entity\ArtistSource;
use App\Entity\Track;
use PHPUnit\Framework\TestCase;

class ArtistTest extends TestCase
{
    public function testConstruct(): void
    {
        $artist = new Artist();

        $this->assertCount(0, $artist->getArtistSources());
        $this->assertCount(0, $artist->getTracks());
    }

    public function testGettersAndSetters(): void
    {
        $artist = new Artist();

        $this->assertNull($artist->getId());

        $result = $artist->setName('Test Artist');
        $this->assertSame($artist, $result);
        $this->assertSame('Test Artist', $artist->getName());
    }

    public function testAddAndRemoveArtistSource(): void
    {
        $artist = new Artist();
        $artistSource = new ArtistSource();

        $result = $artist->addArtistSource($artistSource);
        $this->assertSame($artist, $result);
        $this->assertCount(1, $artist->getArtistSources());
        $this->assertTrue($artist->getArtistSources()->contains($artistSource));
        $this->assertSame($artist, $artistSource->getArtist());

        // Adding the same source again should not duplicate
        $artist->addArtistSource($artistSource);
        $this->assertCount(1, $artist->getArtistSources());

        $result = $artist->removeArtistSource($artistSource);
        $this->assertSame($artist, $result);
        $this->assertCount(0, $artist->getArtistSources());
    }

    public function testAddAndRemoveTrack(): void
    {
        $artist = new Artist();
        $track = new Track();

        $result = $artist->addTrack($track);
        $this->assertSame($artist, $result);
        $this->assertCount(1, $artist->getTracks());
        $this->assertTrue($artist->getTracks()->contains($track));
        $this->assertSame($artist, $track->getArtist());

        // Adding the same track again should not duplicate
        $artist->addTrack($track);
        $this->assertCount(1, $artist->getTracks());

        $removed = $artist->removeTrack($track);
        $this->assertTrue($removed);
        $this->assertCount(0, $artist->getTracks());
    }

    public function testTimeStampableTrait(): void
    {
        $artist = new Artist();
        $artist->setCreatedAt();

        $this->assertInstanceOf(\DateTimeImmutable::class, $artist->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $artist->getUpdatedAt());
    }
}
