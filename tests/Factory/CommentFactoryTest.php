<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\Comment;
use App\Factory\CommentFactory;
use App\Factory\PostFactory;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CommentFactoryTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    public function testCommentFactory(): void
    {
        // CommentFactory::defaults() calls PostFactory::random() and UserFactory::random(),
        // which require at least one persisted record of each in the database.
        PostFactory::createOne();
        UserFactory::createOne();

        $this->assertSame(Comment::class, CommentFactory::class());
        $comment = CommentFactory::new()->withoutPersisting()->create();
        $this->assertInstanceOf(Comment::class, $comment);
    }
}
