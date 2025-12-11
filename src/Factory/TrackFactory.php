<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Track;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Track>
 */
final class TrackFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return Track::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @return array<string, mixed>
     */
    #[\Override]
    protected function defaults(): array
    {
        return [
            'title' => self::faker()->sentence(3),
            'coverUrl' => 'https://www.placeholderimage.eu/api/nature/id/1/260/260',
            'metadata' => [
                'duration' => self::faker()->numberBetween(60, 600),
                'genre' => self::faker()->randomElement(['Pop', 'Rock', 'Jazz', 'Classical']),
            ],
            'artist' => ArtistFactory::new(),
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
}
