<?php

declare(strict_types=1);

namespace App\Service\Track;

use App\ApiResource\Track\TrackInput;
use App\Entity\Track;
use App\Service\ImageService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Gère la persistance de l'entité Track avec déduplication par `songId`.
 *
 * Utiliser toujours ce service plutôt que de créer des entités Track directement, pour éviter les doublons.
 * Les images de couverture sont téléversées vers Azure lors de la première création ; les requêtes ultérieures pour le même
 * songId retournent l'entité existante sans modification.
 */
readonly class TrackService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ImageService $imageService,
    ) {
    }

    /**
     * Retourne une Track existante avec le songId donné, ou en crée et persiste une nouvelle.
     *
     * L'image de couverture est téléversée vers Azure uniquement lors de la première création ; elle n'est pas mise à jour lors des appels suivants.
     * L'entité n'est pas flushée ici — l'appelant est responsable du flush de l'entity manager.
     */
    public function findOrCreate(TrackInput $trackCreateInput): Track
    {
        $track = $this->em->getRepository(Track::class)->findOneBy(['songId' => $trackCreateInput->songId]);

        if (!$track) {
            $track = new Track();
            $track->setSongId($trackCreateInput->songId);
            $track->setTitle($trackCreateInput->title);
            $track->setArtistName($trackCreateInput->artistName);
            $track->setReleaseYear($trackCreateInput->releaseYear);

            if ($trackCreateInput->coverImage) {
                $track->setCoverImage(
                    $this->imageService->saveBase64ToStorage($trackCreateInput->coverImage, 'covers'),
                );
            }

            $this->em->persist($track);
        }

        return $track;
    }
}
