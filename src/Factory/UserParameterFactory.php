<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Enum\VisibilityEnum;
use App\Entity\UserParameter;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<UserParameter>
 */
final class UserParameterFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return UserParameter::class;
    }

    /**
     * @return array<string, mixed>
     */
    #[\Override]
    protected function defaults(): array
    {
        return [
            'followersVisibility' => VisibilityEnum::Public,
            'followingVisibility' => VisibilityEnum::Public,
            'statsVisibility' => VisibilityEnum::Public,
            'playlistVisibility' => VisibilityEnum::Public,
            'likesVisibility' => VisibilityEnum::Public,
            'notifNewFollower' => VisibilityEnum::Public,
            'notifNewLike' => VisibilityEnum::Public,
            'notifNewComment' => VisibilityEnum::Public,
            'notifNewMessage' => VisibilityEnum::Public,
            'createdAt' => new \DateTimeImmutable(),
            'updatedAt' => new \DateTimeImmutable(),
        ];
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this;
    }
}
