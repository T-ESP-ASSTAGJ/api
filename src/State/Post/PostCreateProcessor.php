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
use App\Service\ImageService;
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
        private ImageService $imageService,
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

        // Find or create track based on songId
        $track = $this->em->getRepository(Track::class)->findOneBy(['songId' => $data->songId]);
        $coverImage = $this->imageService->saveBase64Image($data->coverImage, 'track');

        if (!$track) {
            // Create new track if it doesn't exist
            $track = new Track();
            $track->setSongId($data->songId);
            $track->setTitle($data->trackTitle);
            $track->setArtistName($data->artistName);
            $track->setReleaseYear($data->releaseYear);
            $track->setCoverImage($coverImage);

            $this->em->persist($track);
        }

        // Process and save images
        $frontImageUrl = $this->imageService->saveBase64Image($data->frontImage, 'posts');
        $backImageUrl = $this->imageService->saveBase64Image($data->backImage, 'posts');

        $post = new Post();
        $post->setUser($user);
        $post->setCaption($data->caption);
        $post->setTrack($track);
        $post->setFrontImage($frontImageUrl);
        $post->setBackImage($backImageUrl);
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
