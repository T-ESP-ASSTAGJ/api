<?php

declare(strict_types=1);

namespace App\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\User\UserFollowOutput;
use App\Entity\User;
use App\Repository\FollowRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @implements ProviderInterface<UserFollowOutput>
 */
final readonly class UserFollowersProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FollowRepository $followRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return UserFollowOutput[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $uriVariables['id']]);

        if (null === $user) {
            throw new UnauthorizedHttpException('Bearer', 'Authentication required');
        }

        return $this->followRepository->findFollowers((int) $uriVariables['id']);
    }
}
