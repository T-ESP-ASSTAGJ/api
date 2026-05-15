<?php

declare(strict_types=1);

namespace App\Service\Track;

use App\ApiResource\Track\TrackInput;
use App\Entity\Track;
use App\Service\ImageService;
use Doctrine\ORM\EntityManagerInterface;

readonly class TrackService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ImageService $imageService,
    ) {
    }

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
