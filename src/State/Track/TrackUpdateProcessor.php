<?php

declare(strict_types=1);

namespace App\State\Track;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\Track\TrackGetOutput;
use App\ApiResource\Track\TrackUpdateInput;
use App\Entity\Artist;
use App\Entity\Track;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements ProcessorInterface<TrackUpdateInput, TrackGetOutput>
 */
final readonly class TrackUpdateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    /**
     * @param TrackUpdateInput     $data
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return TrackGetOutput
     */
    public function process(mixed $data, ?Operation $operation = null, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof TrackUpdateInput) {
            $trackId = $uriVariables['id'] ?? null;

            if (!$trackId) {
                throw new NotFoundHttpException('Track ID not found');
            }

            $track = $this->em->getRepository(Track::class)->find($trackId);

            if (!$track) {
                throw new NotFoundHttpException('Track not found');
            }

            // Mise à jour uniquement des champs fournis (PATCH)
            if (null !== $data->title) {
                $track->setTitle($data->title);
            }

            if (null !== $data->coverUrl) {
                $track->setCoverUrl($data->coverUrl);
            }

            if (null !== $data->metadata) {
                $track->setMetadata($data->metadata);
            }

            if (null !== $data->artistId) {
                $artist = $this->em->getRepository(Artist::class)->find($data->artistId);
                if (!$artist) {
                    throw new NotFoundHttpException('Artist not found');
                }
                $track->setArtist($artist);
            }

            if (null !== $data->length) {
                $track->setLength($data->length);
            }

            $violations = $this->validator->validate($track);
            if ($violations->count() > 0) {
                throw new ValidationException($violations);
            }

            $this->em->flush();

            return $this->objectMapper->map($track, TrackGetOutput::class);
        }

        return $data;
    }
}
