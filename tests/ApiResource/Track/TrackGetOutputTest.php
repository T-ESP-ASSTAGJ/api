<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Track;

use App\ApiResource\Track\TrackGetOutput;
use App\Entity\Artist;
use App\Entity\Track;
use PHPUnit\Framework\TestCase;

class TrackGetOutputTest extends TestCase
{
    public function testFromEntity(): void
    {
        $artist = new Artist();
        $artist->setName('Test Artist');

        $track = new Track();
        $track->setTitle('Test Track');
        $track->setCoverUrl('https://example.com/cover.jpg');
        $track->setArtist($artist);
        $track->setMetadata(['album' => 'Test Album', 'duration' => 180]);

        // Use reflection to set the ID and timestamps
        $reflection = new \ReflectionClass($track);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($track, 123);

        $createdAt = new \DateTimeImmutable('2025-01-01 10:00:00');
        $updatedAt = new \DateTimeImmutable('2025-01-02 15:30:00');

        $createdAtProperty = $reflection->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($track, $createdAt);

        $updatedAtProperty = $reflection->getProperty('updatedAt');
        $updatedAtProperty->setAccessible(true);
        $updatedAtProperty->setValue($track, $updatedAt);

        $output = TrackGetOutput::fromEntity($track);

        $this->assertInstanceOf(TrackGetOutput::class, $output);
        $this->assertSame(123, $output->id);
        $this->assertSame('Test Track', $output->title);
        $this->assertSame('https://example.com/cover.jpg', $output->coverUrl);
        $this->assertSame($artist, $output->artist);
        $this->assertSame(['album' => 'Test Album', 'duration' => 180], $output->metadata);
        $this->assertSame($createdAt, $output->createdAt);
        $this->assertSame($updatedAt, $output->updatedAt);
    }

    public function testFromEntityWithNullValues(): void
    {
        $artist = new Artist();
        $artist->setName('Test Artist');

        $track = new Track();
        $track->setTitle('Test Track');
        $track->setCoverUrl(null);
        $track->setArtist($artist);
        $track->setMetadata([]);

        // Use reflection to set the ID (timestamps will not be initialized)
        $reflection = new \ReflectionClass($track);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setValue($track, 456);

        // Initialize timestamps to allow getters to work
        $createdAtProperty = $reflection->getProperty('createdAt');
        $createdAtProperty->setValue($track, new \DateTimeImmutable('2025-01-01 00:00:00'));

        $updatedAtProperty = $reflection->getProperty('updatedAt');
        $updatedAtProperty->setValue($track, new \DateTimeImmutable('2025-01-01 00:00:00'));

        $output = TrackGetOutput::fromEntity($track);

        $this->assertInstanceOf(TrackGetOutput::class, $output);
        $this->assertSame(456, $output->id);
        $this->assertSame('Test Track', $output->title);
        $this->assertNull($output->coverUrl);
        $this->assertSame($artist, $output->artist);
        $this->assertSame([], $output->metadata);
        $this->assertInstanceOf(\DateTimeInterface::class, $output->createdAt);
        $this->assertInstanceOf(\DateTimeInterface::class, $output->updatedAt);
    }
}
