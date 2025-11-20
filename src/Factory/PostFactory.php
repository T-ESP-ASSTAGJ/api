<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Post;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Post>
 */
final class PostFactory extends PersistentObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

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
        return [
            'userId' => UserFactory::random()->getId(),
            'track' => [
                'title' => self::faker()->sentence(3),
                'artist' => self::faker()->name(),
                'album' => self::faker()->sentence(2),
                'duration' => self::faker()->numberBetween(120, 420),
                'genre' => self::faker()->word(),
                'plateform' => 'spotify',
                'plateformId' => self::faker()->uuid(),
                'externalUrl' => self::faker()->url(),
                'isrc' => self::faker()->bothify('??-#####-#####'),
            ],
            'songPreviewUrl' => 'https://cdn.pixabay.com/audio/2025/06/09/audio_ce7b7c1612.mp3',
            'location' => self::faker()->country(),
            'photoUrl' => 'https://images.unsplash.com/photo-1511379938547-c1f69419868d?q=80&w=1200&auto=format&fit=crop',
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
