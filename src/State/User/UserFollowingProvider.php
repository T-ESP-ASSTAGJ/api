<?php

declare(strict_types=1);

namespace App\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @implements ProviderInterface<User>
 */
final readonly class UserFollowingProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return array<User>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        /** @var User|null $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $uriVariables['id']]);

        if (null === $user) {
            throw new UnauthorizedHttpException('Bearer', 'Authentication required');
        }

        return $user->getFollowing();
    }
}
