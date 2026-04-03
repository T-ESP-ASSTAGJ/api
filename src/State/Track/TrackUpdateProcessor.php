<?php

declare(strict_types=1);

namespace App\State\Track;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\Track\TrackInput;
use App\Entity\Track;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements ProcessorInterface<TrackInput, Track>
 */
final readonly class TrackUpdateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * @param TrackInput           $data
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return Track
     *
     * @Deprecated
     */
    public function process(mixed $data, ?Operation $operation = null, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof TrackInput) {
            return new BadRequestException('Invalid payload');
        }

        $trackId = $uriVariables['id'] ?? null;

        if (!$trackId) {
            throw new NotFoundHttpException('Track ID not found');
        }

        $track = $this->em->getRepository(Track::class)->find($trackId);

        if (!$track) {
            throw new NotFoundHttpException('Track not found');
        }

        $track->setSongId($data->songId);

        $track->setTitle($data->title);

        $track->setArtistName($data->artistName);

        $track->setReleaseYear($data->releaseYear);

        $violations = $this->validator->validate($track);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $this->em->flush();

        return $track;
    }
}
