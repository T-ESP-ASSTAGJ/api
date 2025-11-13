<?php

declare(strict_types=1);

namespace App\State\Artist;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\Artist\ArtistUpdateInput;
use App\Entity\Artist;
use App\Entity\ArtistSource;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements ProcessorInterface<ArtistUpdateInput, Artist>
 */
final readonly class ArtistUpdateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * @param ArtistUpdateInput    $data
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return Artist
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
            if (null !== $data->name) {
                $artist->setName($data->name);
            }

            // Mise à jour des ArtistSource si fournis
            if (null !== $data->artistSources) {
                // Supprimer les anciennes sources
                foreach ($artist->getArtistSources() as $oldSource) {
                    $artist->removeArtistSource($oldSource);
                }

                // Ajouter les nouvelles sources
                foreach ($data->artistSources as $sourceDto) {
                    $artistSource = new ArtistSource();
                    $artistSource->setPlatform($sourceDto->platform);
                    $artistSource->setPlatformArtistId($sourceDto->platformArtistId);
                    $artist->addArtistSource($artistSource);
                }
            }

            $violations = $this->validator->validate($artist);
            if ($violations->count() > 0) {
                throw new ValidationException($violations);
            }

            $this->em->flush();

            return $artist;
        }

        return $data;
    }
}
