<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Post;

use App\ApiResource\Post\PostCreateInput;
use PHPUnit\Framework\TestCase;

class PostCreateInputTest extends TestCase
{
    public function testPublicProperties(): void
    {
        $input = new PostCreateInput();
        $input->caption = 'Amazing sunset vibes!';
        $input->songId = '42';
        $input->coverImage = 'https://example.com/photo.jpg';
        $input->location = 'Paris, France';

        $this->assertSame('Amazing sunset vibes!', $input->caption);
        $this->assertSame('42', $input->songId);
        $this->assertSame('https://example.com/photo.jpg', $input->coverImage);
        $this->assertSame('Paris, France', $input->location);
    }

    public function testDefaultValues(): void
    {
        $input = new PostCreateInput();

        $this->assertNull($input->caption);
        $this->assertNull($input->coverImage);
        $this->assertNull($input->location);
    }

    public function testCanSetNullableFields(): void
    {
        $input = new PostCreateInput();
        $input->songId = '1';
        $input->caption = null;
        $input->coverImage = null;
        $input->location = null;

        $this->assertNull($input->caption);
        $this->assertNull($input->coverImage);
        $this->assertNull($input->location);
        $this->assertSame('1', $input->songId);
    }
}
