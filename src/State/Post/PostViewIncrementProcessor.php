<?php

declare(strict_types=1);

namespace App\State\Post;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Post;
use App\Entity\User;
use App\Message\PersistPostViewsMessage;
use App\Service\Post\PostViewCounter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @implements ProcessorInterface<Post, Post>
 */
final readonly class PostViewIncrementProcessor implements ProcessorInterface
{
    public function __construct(
        private PostViewCounter $postViewCounter,
        private Security $security,
        private MessageBusInterface $messageBus,
    ) {
    }

    /**
     * @param Post                 $data
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @throws ExceptionInterface
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Post
    {
        if (!$data instanceof Post) {
            throw new \RuntimeException('Expected data to be an instance of Post.');
        }

        if (null === $data->getId()) {
            return $data;
        }

        /** @var User|null $user */
        $user = $this->security->getUser();

        if ($user) {
            $this->postViewCounter->increment($data, $user->getId());
        }

        $currentViews = $this->postViewCounter->getViews($data);

        $data->setViewsCount($currentViews);

        $this->messageBus->dispatch(new PersistPostViewsMessage($data->getId()));

        return $data;
    }
}
