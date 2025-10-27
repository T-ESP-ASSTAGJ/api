<?php

declare(strict_types=1);

namespace App\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\User\UserGetOutput;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

/**
 * @implements ProviderInterface<UserGetOutput>
 */
final readonly class UserGetCollectionProvider implements ProviderInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private ObjectMapperInterface $mapper,
    ) {
    }

    /**
     * @return UserGetOutput[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        /** @var User[] $users */
        $users = $this->userRepository->findAll();

        return array_map(
            fn (User $user) => $this->mapper->map($user, UserGetOutput::class),
            $users
        );
    }
}
