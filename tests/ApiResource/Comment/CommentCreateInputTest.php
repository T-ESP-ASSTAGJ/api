<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Comment;

use App\ApiResource\Comment\CommentCreateInput;
use PHPUnit\Framework\TestCase;

class CommentCreateInputTest extends TestCase
{
    public function testPublicProperties(): void
    {
        $input = new CommentCreateInput();
        $input->content = 'This is a test comment';

        $this->assertSame('This is a test comment', $input->content);
    }

    public function testCanSetContent(): void
    {
        $input = new CommentCreateInput();
        $input->content = 'Super morceau ! J\'adore cette mélodie.';

        $this->assertSame('Super morceau ! J\'adore cette mélodie.', $input->content);
    }
}
