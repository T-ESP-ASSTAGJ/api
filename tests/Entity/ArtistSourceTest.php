<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Artist;
use App\Entity\ArtistSource;
use PHPUnit\Framework\TestCase;

class ArtistSourceTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $artistSource = new ArtistSource();
        $artist = new Artist();

        $this->assertNull($artistSource->getId());

        $result = $artistSource->setArtist($artist);
        $this->assertSame($artistSource, $result);
        $this->assertSame($artist, $artistSource->getArtist());

        $result = $artistSource->setPlatform('spotify');
        $this->assertSame($artistSource, $result);
        $this->assertSame('spotify', $artistSource->getPlatform());

        $result = $artistSource->setPlatformArtistId('spotify123');
        $this->assertSame($artistSource, $result);
        $this->assertSame('spotify123', $artistSource->getPlatformArtistId());
    }

    public function testTimeStampableTrait(): void
    {
        $artistSource = new ArtistSource();
        $artistSource->setCreatedAt();

        $this->assertInstanceOf(\DateTimeImmutable::class, $artistSource->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $artistSource->getUpdatedAt());
    }
}
