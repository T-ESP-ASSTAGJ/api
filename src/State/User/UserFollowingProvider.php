<?php

declare(strict_types=1);

namespace App\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Follow;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
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
     * @return array<array{id: int|null, username: string|null, profilePicture: string|null}>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $uriVariables['id']]);

        if (null === $user) {
            throw new UnauthorizedHttpException('Bearer', 'Authentication required');
        }

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('followed.id', 'followed.username', 'followed.profilePicture')
            ->from(Follow::class, 'f')
            ->join('f.followedUser', 'followed')
            ->where('f.follower = :userId')
            ->setParameter('userId', $uriVariables['id']);

        return $qb->getQuery()->getResult();
    }
}
