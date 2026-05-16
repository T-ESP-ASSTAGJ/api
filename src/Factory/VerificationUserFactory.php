<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\VerificationUser;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<VerificationUser>
 */
final class VerificationUserFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return VerificationUser::class;
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
            'email' => self::faker()->email(),
            'code' => self::faker()->randomNumber(5),
            'expiresAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }
}
