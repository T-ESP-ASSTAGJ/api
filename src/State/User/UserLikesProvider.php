<?php

declare(strict_types=1);

namespace App\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Like;
use App\Entity\User;
use App\Repository\LikeRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @implements ProviderInterface<User>
 */
final readonly class UserLikesProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return array<array<string, mixed>>  <-- Note the change in return type
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        /** @var User|null $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $uriVariables['id']]);

        if (null === $user) {
            throw new NotFoundHttpException('User not found');
        }

        /** @var LikeRepository $likeRepository */
        $likeRepository = $this->entityManager->getRepository(Like::class);

        return $likeRepository->findByUser($user);
    }
}
