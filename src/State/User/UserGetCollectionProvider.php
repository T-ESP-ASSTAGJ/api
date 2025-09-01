<?php

declare(strict_types=1);

namespace App\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\DTO\User\UserGetOutput;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

final readonly class UserGetCollectionProvider implements ProviderInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private ObjectMapperInterface $mapper,
    ) {
    }

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
