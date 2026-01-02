<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Factory\CommentFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class Comment extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        CommentFactory::createMany(150);
    }

    public function getDependencies(): array
    {
        return [
            User::class,
            Post::class,
        ];
    }
}
