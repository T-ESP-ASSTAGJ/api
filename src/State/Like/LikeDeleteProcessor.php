<?php

declare(strict_types=1);

namespace App\State\Like;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Like\LikeCreateInput;
use App\Entity\Like;
use App\Entity\User;
use App\Repository\LikeRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<LikeCreateInput, void>
 */
final readonly class LikeDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        /** @var ProcessorInterface<Like, void> */
        #[Autowire(service: 'api_platform.doctrine.orm.state.remove_processor')]
        private ProcessorInterface $removeProcessor,
        private LikeRepository $likeRepository,
        private Security $security,
    ) {
    }

    /**
     * @param LikeCreateInput      $data
     * @param Operation|null       $operation
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function process($data, $operation, array $uriVariables = [], array $context = []): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        dump($data, $user);
        $like = $this->likeRepository->findOneBy([
            'user' => $user,
            'entityClass' => $data->entityClass->value,
            'entityId' => $data->entityId,
        ]);

        if (!$like) {
            throw new NotFoundHttpException('Like not found.');
        }

        $this->removeProcessor->process(
            $like,
            $operation,
            $uriVariables,
            $context
        );
    }
}
