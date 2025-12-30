<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\User;
use Faker\Factory;
use Mmo\Faker\PicsumProvider;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<User>
 */
final class UserFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return User::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     *
     * @return array<string, mixed>
     */
    #[\Override]
    protected function defaults(): array
    {
        $faker = Factory::create();
        $faker->addProvider(new PicsumProvider($faker));

        return [
            'email' => self::faker()->email(),
            'isVerified' => self::faker()->boolean(),
            'needsProfile' => self::faker()->boolean(),
            'username' => self::faker()->userName(),
            'bio' => self::faker()->realText(200),
            'profile_picture' => $faker->picsumStaticRandomUrl(1920, 1080),
            'phone_number' => self::faker()->unique()->phoneNumber(),
            'roles' => [],
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(User $user): void {})
        ;
    }

    public function testUser(): static
    {
        return $this->with([
            'email' => 'test@example.com',
            'username' => 'testuser',
            'password' => 'password123',
            'bio' => 'This is a test user',
            'isVerified' => true,
        ]);
    }
}
