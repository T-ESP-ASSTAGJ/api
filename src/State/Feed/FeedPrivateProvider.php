<?php

declare(strict_types=1);

namespace App\State\Feed;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ProviderInterface<Post>
 */
final readonly class FeedPrivateProvider implements ProviderInterface
{
    public function __construct(
        private Security $security,
        #[Autowire(service: 'api_platform.pagination')]
        private Pagination $pagination,
        private PostRepository $postRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return array<Post>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $currentUser = $this->security->getUser();

        if (!$currentUser instanceof User) {
            throw new \RuntimeException('Authentication required');
        }

        $followedUsers = $currentUser->getFollowed();
        $followedUserIds = array_filter(array_map(static fn ($user) => $user['id'], $followedUsers));

        if (empty($followedUserIds)) {
            return [];
        }

        $offset = $this->pagination->getOffset($operation, $context);
        $limit = $this->pagination->getLimit($operation, $context);

        return $this->postRepository->getFollowedPaginatedPosts($followedUserIds, $offset, $limit);
    }
}
