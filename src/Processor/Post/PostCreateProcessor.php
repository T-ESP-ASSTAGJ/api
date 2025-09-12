<?php

declare(strict_types=1);

namespace App\Processor\Post;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\DTO\Post\PostCreateInput;
use App\DTO\Post\PostGetOutput;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements ProcessorInterface<PostCreateInput, PostGetOutput>
 */
final readonly class PostCreateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    /**
     * @param PostCreateInput      $data
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return PostGetOutput
     */
    public function process(mixed $data, ?Operation $operation = null, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof PostCreateInput) {
            $post = new Post();
            $post->setUserId($data->userId);
            $post->setSongPreviewUrl($data->songPreviewUrl);
            $post->setCaption($data->caption);
            $post->setTrack($data->track);
            $post->setPhotoUrl($data->photoUrl);
            $post->setLocation($data->location);

            $violations = $this->validator->validate($post);
            if ($violations->count() > 0) {
                throw new ValidationException($violations);
            }

            $this->em->persist($post);
            $this->em->flush();

            return $this->objectMapper->map($post, PostGetOutput::class);
        }

        return $data;
    }
}
