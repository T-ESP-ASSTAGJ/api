<?php

declare(strict_types=1);

namespace App\State\Feed;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Post\PostGetOutput;
use App\Repository\PostRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ProviderInterface<PostGetOutput>
 */
final readonly class FeedPublicProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.pagination')]
        private Pagination $pagination,
        private PostRepository $postRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return array<PostGetOutput>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $offset = $this->pagination->getOffset($operation, $context);
        $limit = $this->pagination->getLimit($operation, $context);

        $posts = $this->postRepository->getPaginatedPosts($offset, $limit);

        $feedOutputs = [];
        foreach ($posts as $post) {
            $output = new PostGetOutput();
            $output->id = $post->getId();
            $output->user = $post->getUser();
            $output->caption = $post->getCaption();
            $output->track = $post->getTrack();
            $output->photoUrl = $post->getPhotoUrl();
            $output->location = $post->getLocation();
            $output->createdAt = $post->getCreatedAt();
            $output->updatedAt = $post->getUpdatedAt();

            $feedOutputs[] = $output;
        }

        return $feedOutputs;
    }
}
