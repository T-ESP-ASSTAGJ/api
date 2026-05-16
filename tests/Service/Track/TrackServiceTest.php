<?php

declare(strict_types=1);

namespace App\Tests\Service\Track;

use App\ApiResource\Track\TrackInput;
use App\Entity\Track;
use App\Service\ImageService;
use App\Service\Track\TrackService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class TrackServiceTest extends TestCase
{
    private EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject $em;

    private ImageService&\PHPUnit\Framework\MockObject\MockObject $imageService;

    private TrackService $service;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->imageService = $this->createMock(ImageService::class);
        $this->service = new TrackService($this->em, $this->imageService);
    }

    public function testFindOrCreateReturnsExistingTrack(): void
    {
        $existingTrack = new Track();

        /** @var EntityRepository<Track>&\PHPUnit\Framework\MockObject\MockObject $repo */
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->with(['songId' => 'abc123'])->willReturn($existingTrack);

        $this->em->method('getRepository')->with(Track::class)->willReturn($repo);
        $this->em->expects($this->never())->method('persist');
        $this->imageService->expects($this->never())->method('saveBase64ToStorage');

        $input = new TrackInput('abc123', 'Title', 'Artist');
        $result = $this->service->findOrCreate($input);

        $this->assertSame($existingTrack, $result);
    }

    public function testFindOrCreateCreatesNewTrackWithoutCoverImage(): void
    {
        /** @var EntityRepository<Track>&\PHPUnit\Framework\MockObject\MockObject $repo */
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        $this->em->method('getRepository')->willReturn($repo);
        $this->em->expects($this->once())->method('persist')->with($this->isInstanceOf(Track::class));
        $this->imageService->expects($this->never())->method('saveBase64ToStorage');

        $input = new TrackInput('new-song', 'New Title', 'New Artist', 2024);
        $result = $this->service->findOrCreate($input);

        $this->assertInstanceOf(Track::class, $result);
        $this->assertSame('new-song', $result->getSongId());
        $this->assertSame('New Title', $result->getTitle());
    }

    public function testFindOrCreateCreatesNewTrackWithCoverImage(): void
    {
        /** @var EntityRepository<Track>&\PHPUnit\Framework\MockObject\MockObject $repo */
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        $this->em->method('getRepository')->willReturn($repo);
        $this->em->expects($this->once())->method('persist');

        $this->imageService->expects($this->once())
            ->method('saveBase64ToStorage')
            ->with('base64data', 'covers')
            ->willReturn('/covers/image.jpg')
        ;

        $input = new TrackInput('song-with-cover', 'Title', 'Artist', null, 'base64data');
        $result = $this->service->findOrCreate($input);

        $this->assertSame('/covers/image.jpg', $result->getCoverImage());
    }
}
