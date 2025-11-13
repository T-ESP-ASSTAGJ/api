<?php

declare(strict_types=1);

namespace App\State\Track;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\Track\TrackCreateInput;
use App\ApiResource\Track\TrackGetOutput;
use App\Entity\Artist;
use App\Entity\Track;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements ProcessorInterface<TrackCreateInput, TrackGetOutput>
 */
final readonly class TrackCreateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    /**
     * @param TrackCreateInput     $data
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return TrackGetOutput
     */
    public function process(mixed $data, ?Operation $operation = null, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof TrackCreateInput) {
            // Vérifier que l'artiste existe
            $artist = $this->em->getRepository(Artist::class)->find($data->artistId);
            if (!$artist) {
                throw new NotFoundHttpException('Artist not found');
            }

            $track = new Track();
            $track->setTitle($data->title);
            $track->setCoverUrl($data->coverUrl);
            $track->setMetadata($data->metadata);
            $track->setArtist($artist);
            $track->setLength($data->length);

            $violations = $this->validator->validate($track);
            if ($violations->count() > 0) {
                throw new ValidationException($violations);
            }

            $this->em->persist($track);
            $this->em->flush();

            return $this->objectMapper->map($track, TrackGetOutput::class);
        }

        return $data;
    }
}
