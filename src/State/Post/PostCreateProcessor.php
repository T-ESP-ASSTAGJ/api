<?php

declare(strict_types=1);

namespace App\State\Post;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\Post\PostCreateInput;
use App\Entity\Post;
use App\Entity\Track;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements ProcessorInterface<PostCreateInput, Post>
 */
final readonly class PostCreateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
        private Security $security,
    ) {
    }

    /**
     * @param PostCreateInput      $data
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return Post
     */
    public function process(mixed $data, ?Operation $operation = null, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof PostCreateInput) {
            return $data;
        }

        /** @var User|null $user */
        $user = $this->security->getUser();

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        $track = $this->em->getRepository(Track::class)->find($data->trackId);
        if (!$track) {
            throw new NotFoundHttpException('Track not found');
        }

        $post = new Post();
        $post->setUser($user);
        $post->setCaption($data->caption);
        $post->setTrack($track);
        $post->setPhotoUrl($data->photoUrl);
        $post->setLocation($data->location);

        $violations = $this->validator->validate($post);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $this->em->persist($post);
        $this->em->flush();

        return $post;
    }
}
