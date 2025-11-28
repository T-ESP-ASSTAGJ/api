<?php

declare(strict_types=1);

namespace App\State\Feed;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Post\PostGetOutput;
use App\Entity\User;
use App\Repository\PostRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ProviderInterface<PostGetOutput>
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
     * @return iterable<PostGetOutput>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $currentUser = $this->security->getUser();

        if (!$currentUser instanceof User) {
            throw new \RuntimeException('Authentication required');
        }

        $followedUsers = $currentUser->getFollowed();
        $followedUserIds = array_filter(array_map(fn ($user) => $user['id'], $followedUsers));

        if (empty($followedUserIds)) {
            return [];
        }

        $offset = $this->pagination->getOffset($operation, $context);
        $limit = $this->pagination->getLimit($operation, $context);

        $posts = $this->postRepository->getFollowedPaginatedPosts($followedUserIds, $offset, $limit);

        $feedOutputs = [];
        foreach ($posts as $post) {
            $output = new PostGetOutput();
            $output->id = $post->getId();
            $output->userId = $post->getUserId();
            $output->songPreviewUrl = $post->getSongPreviewUrl();
            $output->caption = $post->getCaption();
            // $output->track = $post->getTrack();
            $output->photoUrl = $post->getPhotoUrl();
            $output->location = $post->getLocation();
            $output->createdAt = $post->getCreatedAt();
            $output->updatedAt = $post->getUpdatedAt();

            $feedOutputs[] = $output;
        }

        return $feedOutputs;
    }
}
