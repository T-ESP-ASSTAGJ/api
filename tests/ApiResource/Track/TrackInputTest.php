<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Track;

use App\ApiResource\Track\TrackInput;
use PHPUnit\Framework\TestCase;

class TrackInputTest extends TestCase
{
    public function testConstructorWithRequiredParameters(): void
    {
        $input = new TrackInput(
            songId: 'track_123',
            title: 'Blinding Lights',
            artistName: 'The Weeknd',
        );

        $this->assertSame('track_123', $input->songId);
        $this->assertSame('Blinding Lights', $input->title);
        $this->assertSame('The Weeknd', $input->artistName);
        $this->assertNull($input->releaseYear);
        $this->assertNull($input->coverImage);
    }

    public function testConstructorWithAllParameters(): void
    {
        $input = new TrackInput(
            songId: 'track_456',
            title: 'Shape of You',
            artistName: 'Ed Sheeran',
            releaseYear: 2017,
            coverImage: 'data:image/jpeg;base64,/9j/4AAQSkZJRgAB',
        );

        $this->assertSame('track_456', $input->songId);
        $this->assertSame('Shape of You', $input->title);
        $this->assertSame('Ed Sheeran', $input->artistName);
        $this->assertSame(2017, $input->releaseYear);
        $this->assertSame('data:image/jpeg;base64,/9j/4AAQSkZJRgAB', $input->coverImage);
    }
}
