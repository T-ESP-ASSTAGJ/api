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
            'caption' => self::faker()->optional(0.7)->realText(200),
            // @phpstan-ignore method.notFound
            'frontImage' => $faker->picsumStaticRandomUrl(1920, 1080),
            // @phpstan-ignore method.notFound
            'backImage' => self::faker()->optional(0.5)->passthrough($faker->picsumStaticRandomUrl(1920, 1080)),
            'location' => self::faker()->optional(0.8)->country(),
            'commentsCount' => 0,
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
