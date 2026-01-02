<?php

declare(strict_types=1);

namespace App\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Enum\LikeableTypeEnum;
use App\Entity\Like;
use App\Entity\Post;
use App\Entity\User;
use App\Service\isLikedEnricher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<Post>
 */
final readonly class UserLikedPostProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private isLikedEnricher $isLikedEnricher,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return Post[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $user = $this->entityManager->getRepository(User::class)->find($uriVariables['id']);

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        /** @var Like[] $likes */
        $likes = $this->entityManager->getRepository(Like::class)->findBy([
            'user' => $uriVariables['id'],
            'entityClass' => LikeableTypeEnum::Post,
        ]);

        if (empty($likes)) {
            return [];
        }

        $postIds = array_map(static fn (Like $like) => $like->getEntityId(), $likes);

        $posts = $this->entityManager->getRepository(Post::class)->findBy(['id' => $postIds]);

        $this->isLikedEnricher->enrich($posts, Post::class);

        return $posts;
    }
}
