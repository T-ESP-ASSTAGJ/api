<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Post;

use App\ApiResource\Post\PostCreateInput;
use App\ApiResource\Track\TrackInput;
use PHPUnit\Framework\TestCase;

class PostCreateInputTest extends TestCase
{
    public function testPublicProperties(): void
    {
        $trackInput = new TrackInput(
            songId: '1',
            title: 'Tung tung sahur',
            artistName: 'John Doe',
            coverImage: 'https://example.com/photo.jpg',
        );
        $input = new PostCreateInput(
            track: $trackInput,
            caption: 'Brainrot!',
            location: 'Paris, France',
        );

        $this->assertSame('Brainrot!', $input->caption);
        $this->assertSame('1', $input->track->songId);
        $this->assertSame('Tung tung sahur', $input->track->title);
        $this->assertSame('John Doe', $input->track->artistName);
        $this->assertSame('https://example.com/photo.jpg', $input->track->coverImage);
        $this->assertSame('Paris, France', $input->location);
    }

    public function testDefaultValues(): void
    {
        $trackInput = new TrackInput(
            songId: '1',
            title: 'Tung tung sahur',
            artistName: 'John Doe',
        );

        $input = new PostCreateInput(
            track: $trackInput,
        );

        $this->assertNull($input->caption);
        $this->assertNull($input->track->coverImage);
        $this->assertNull($input->track->releaseYear);
        $this->assertNull($input->location);
        $this->assertNull($input->frontImage);
        $this->assertNull($input->backImage);
    }
}
