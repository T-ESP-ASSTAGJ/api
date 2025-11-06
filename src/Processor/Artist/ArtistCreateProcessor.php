<?php

declare(strict_types=1);

namespace App\Processor\Artist;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\DTO\Artist\ArtistCreateInput;
use App\DTO\Artist\ArtistGetOutput;
use App\Entity\Artist;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements ProcessorInterface<ArtistCreateInput, ArtistGetOutput>
 */
final readonly class ArtistCreateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    /**
     * @param ArtistCreateInput    $data
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return ArtistGetOutput
     */
    public function process(mixed $data, ?Operation $operation = null, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof ArtistCreateInput) {
            $artist = new Artist();
            $artist->setName($data->name);
            $artist->setMetadata($data->metadata);

            $violations = $this->validator->validate($artist);
            if ($violations->count() > 0) {
                throw new ValidationException($violations);
            }

            $this->em->persist($artist);
            $this->em->flush();

            return $this->objectMapper->map($artist, ArtistGetOutput::class);
        }

        return $data;
    }
}