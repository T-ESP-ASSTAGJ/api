<?php

declare(strict_types=1);

namespace App\State\Track;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\Track\TrackCreateInput;
use App\Entity\Track;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements ProcessorInterface<TrackCreateInput, Track>
 */
final readonly class TrackCreateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * @param TrackCreateInput     $data
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return Track
     * @Deprecated
     */
    public function process(mixed $data, ?Operation $operation = null, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof TrackCreateInput) {
            $track = new Track();
            $track->setSongId($data->songId);
            $track->setTitle($data->title);
            $track->setArtistName($data->artistName);
            $track->setReleaseYear($data->releaseYear);

            $violations = $this->validator->validate($track);
            if ($violations->count() > 0) {
                throw new ValidationException($violations);
            }

            $this->em->persist($track);
            $this->em->flush();

            return $track;
        }

        return $data;
    }
}
