<?php

declare(strict_types=1);

namespace App\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\DTO\User\UserGetOutput;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

final readonly class UserGetProvider implements ProviderInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private ObjectMapperInterface $mapper,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?UserGetOutput
    {
        $id = $uriVariables['id'] ?? null;

        if (!$id) {
            return null;
        }

        /** @var User|null $user */
        $user = $this->userRepository->find($id);

        if (!$user) {
            return throw new NotFoundHttpException('User not found.');
        }

        return $this->mapper->map($user, UserGetOutput::class);
    }
}
