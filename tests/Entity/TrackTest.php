<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Artist;
use App\Entity\Track;
use App\Entity\TrackSource;
use PHPUnit\Framework\TestCase;

class TrackTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $track = new Track();
        $artist = new Artist();

        $this->assertNull($track->getId());

        $result = $track->setTitle('Test Track');
        $this->assertSame($track, $result);
        $this->assertSame('Test Track', $track->getTitle());

        $result = $track->setCoverUrl('https://example.com/cover.jpg');
        $this->assertSame($track, $result);
        $this->assertSame('https://example.com/cover.jpg', $track->getCoverUrl());

        $metadata = ['duration' => 180, 'genre' => 'Rock'];
        $result = $track->setMetadata($metadata);
        $this->assertSame($track, $result);
        $this->assertSame($metadata, $track->getMetadata());

        $result = $track->setArtist($artist);
        $this->assertSame($track, $result);
        $this->assertSame($artist, $track->getArtist());
    }

    public function testTrackSourceCollection(): void
    {
        $track = new Track();
        $trackSource1 = new TrackSource();
        $trackSource2 = new TrackSource();

        $this->assertCount(0, $track->getTrackSources());

        $result = $track->addTrackSource($trackSource1);
        $this->assertSame($track, $result);
        $this->assertCount(1, $track->getTrackSources());
        $this->assertTrue($track->getTrackSources()->contains($trackSource1));

        $track->addTrackSource($trackSource2);
        $this->assertCount(2, $track->getTrackSources());

        // Test adding same source twice (should not duplicate)
        $track->addTrackSource($trackSource1);
        $this->assertCount(2, $track->getTrackSources());

        $result = $track->removeTrackSource($trackSource1);
        $this->assertSame($track, $result);
        $this->assertCount(1, $track->getTrackSources());
        $this->assertFalse($track->getTrackSources()->contains($trackSource1));
    }

    public function testTimeStampableTrait(): void
    {
        $track = new Track();
        $track->setCreatedAt();

        $this->assertInstanceOf(\DateTimeImmutable::class, $track->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $track->getUpdatedAt());
    }

    public function testDefaultMetadata(): void
    {
        $track = new Track();
        $this->assertSame([], $track->getMetadata());
    }
}
