<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Factory\PostFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class Post extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        PostFactory::createMany(500);
    }

    public function getDependencies(): array
    {
        return [
            User::class,
        ];
    }
}
