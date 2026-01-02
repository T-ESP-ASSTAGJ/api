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
        $input->trackId = 42;
        $input->photoUrl = 'https://example.com/photo.jpg';
        $input->location = 'Paris, France';

        $this->assertSame('Amazing sunset vibes!', $input->caption);
        $this->assertSame(42, $input->trackId);
        $this->assertSame('https://example.com/photo.jpg', $input->photoUrl);
        $this->assertSame('Paris, France', $input->location);
    }

    public function testDefaultValues(): void
    {
        $input = new PostCreateInput();

        $this->assertNull($input->caption);
        $this->assertNull($input->photoUrl);
        $this->assertNull($input->location);
    }

    public function testCanSetNullableFields(): void
    {
        $input = new PostCreateInput();
        $input->trackId = 1;
        $input->caption = null;
        $input->photoUrl = null;
        $input->location = null;

        $this->assertNull($input->caption);
        $this->assertNull($input->photoUrl);
        $this->assertNull($input->location);
        $this->assertSame(1, $input->trackId);
    }
}
