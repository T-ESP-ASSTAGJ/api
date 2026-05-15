<?php

declare(strict_types=1);

namespace App\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\User;
use App\Entity\UserParameter;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProviderInterface<UserParameter>
 */
final readonly class UserParameterProvider implements ProviderInterface
{
    public function __construct(
        private Security $security,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?UserParameter
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        return $user?->getParameters();
    }
}
