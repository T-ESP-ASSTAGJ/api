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
            'coverUrl' => self::faker()->randomElement([
                'https://fastly.picsum.photos/id/203/200/200.jpg?hmac=fydyJjsULq7iMwTTIg_m6g_PQQ1paJrufNsEiqbJRsg',
                'https://fastly.picsum.photos/id/340/200/200.jpg?hmac=8wghWAHHnmRpcl_sZApJLzQLqryeZqjaGMZo2wlZS3M',
                'https://fastly.picsum.photos/id/635/200/200.jpg?hmac=Vm8Tavc31Qax01634w3MOPpNCCfasJG8wnBamSi87T4',
                'https://fastly.picsum.photos/id/979/200/200.jpg?hmac=WcPMB8O2ujsPsQzJm14ISP-kXmQ59P6G82VPGNwql4I',
                'https://fastly.picsum.photos/id/90/200/200.jpg?hmac=zltjAmHceKvUbRnvGycGPocNMsLFu-jiTwBEcre1_pU',
            ]),
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
