<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class User extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        UserFactory::createOne([
            'email' => 'test@example.com',
            'username' => 'testUser',
            'bio' => 'This is a test user',
            'isVerified' => true,
        ]);

        UserFactory::createMany(9);

        $manager->flush();
    }
}
