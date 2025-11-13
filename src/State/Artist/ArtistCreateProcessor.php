<?php

declare(strict_types=1);

namespace App\State\Artist;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\Artist\ArtistCreateInput;
use App\Entity\Artist;
use App\Entity\ArtistSource;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements ProcessorInterface<ArtistCreateInput, Artist>
 */
final readonly class ArtistCreateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * @param ArtistCreateInput    $data
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return Artist
     */
    public function process(mixed $data, ?Operation $operation = null, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof ArtistCreateInput) {
            $artist = new Artist();
            $artist->setName($data->name);

            // Création des ArtistSource
            foreach ($data->artistSources as $sourceDto) {
                $artistSource = new ArtistSource();
                $artistSource->setPlatform($sourceDto->platform);
                $artistSource->setPlatformArtistId($sourceDto->platformArtistId);
                $artist->addArtistSource($artistSource);
            }

            $violations = $this->validator->validate($artist);
            if ($violations->count() > 0) {
                throw new ValidationException($violations);
            }

            $this->em->persist($artist);
            $this->em->flush();

            return $artist;
        }

        return $data;
    }
}
