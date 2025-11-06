<?php


declare(strict_types=1);

namespace App\Processor\Artist;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\DTO\Artist\ArtistGetOutput;
use App\DTO\Artist\ArtistUpdateInput;
use App\Entity\Artist;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements ProcessorInterface<ArtistUpdateInput, ArtistGetOutput>
 */
final readonly class ArtistUpdateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface     $validator,
        private ObjectMapperInterface  $objectMapper,
    )
    {
    }

    /**
     * @param ArtistUpdateInput $data
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return ArtistGetOutput
     */
    public function process(mixed $data, ?Operation $operation = null, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof ArtistUpdateInput) {
            $artistId = $uriVariables['id'] ?? null;

            if (!$artistId) {
                throw new NotFoundHttpException('Artist ID not found');
            }

            $artist = $this->em->getRepository(Artist::class)->find($artistId);

            if (!$artist) {
                throw new NotFoundHttpException('Artist not found');
            }

            // Mise à jour uniquement des champs fournis (PATCH)
            if ($data->name !== null) {
                $artist->setName($data->name);
            }

            if ($data->metadata !== null) {
                $artist->setMetadata($data->metadata);
            }

            $violations = $this->validator->validate($artist);
            if ($violations->count() > 0) {
                throw new ValidationException($violations);
            }

            $this->em->flush();

            return $this->objectMapper->map($artist, ArtistGetOutput::class);
        }

        return $data;
    }
}