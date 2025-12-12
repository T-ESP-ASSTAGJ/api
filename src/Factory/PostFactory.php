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
            'user' => UserFactory::random(),
            'track' => TrackFactory::new(),
            'location' => self::faker()->country(),
            'photoUrl' => self::faker()->randomElement([
                'https://images.unsplash.com/photo-1511379938547-c1f69419868d?q=80&w=1200&auto=format&fit=crop',
                'https://fastly.picsum.photos/id/47/200/200.jpg?hmac=dF66rvzPwuJCh4L7IjS6I0D5xrpPvqhAjbE7FstnEnY',
                'https://fastly.picsum.photos/id/485/200/200.jpg?hmac=7ho6uS1u-Lmj8IR2V6-nJaiAVicTYT7bNcnzCMRwEG4',
                'https://fastly.picsum.photos/id/1075/200/200.jpg?hmac=a9PcCsXBonPZ7LCLyWX6dHM1XGbcojML0qhnq-Ee4a4',
                'https://fastly.picsum.photos/id/570/200/200.jpg?hmac=fgqmD9u8TqyXJG9fhqV-EbhIUXYwTIxfsPiNfaD28_Y',
            ]),
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
