<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Post;
use Faker\Factory;
use Mmo\Faker\PicsumProvider;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Post>
 */
final class PostFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return Post::class;
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
            'user' => UserFactory::random(),
            'track' => TrackFactory::new(),
            'location' => self::faker()->country(),
            // @phpstan-ignore method.notFound
            'photoUrl' => $faker->picsumStaticRandomUrl(1920, 1080),
            'caption' => self::faker()->realText(200),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Post $post): void {})
        ;
    }
}
