<?php

declare(strict_types=1);

namespace App\Tests\State\Artist;

use ApiPlatform\Metadata\Patch;
use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\Artist\ArtistSourceDto;
use App\ApiResource\Artist\ArtistUpdateInput;
use App\Entity\Artist;
use App\Entity\ArtistSource;
use App\State\Artist\ArtistUpdateProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ArtistUpdateProcessorTest extends TestCase
{
    /**
     * @var EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private EntityManagerInterface $em;

    /**
     * @var ValidatorInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private ValidatorInterface $validator;

    private ArtistUpdateProcessor $processor;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->processor = new ArtistUpdateProcessor($this->em, $this->validator);
    }

    public function testUpdatesArtistName(): void
    {
        $artist = $this->makeArtist();
        $this->mockRepo($artist);
        $this->validator->method('validate')->willReturn(new ConstraintViolationList());
        $this->em->expects($this->once())->method('flush');

        $input = new ArtistUpdateInput();
        $input->name = 'New Name';

        $result = $this->processor->process($input, new Patch(), ['id' => 1]);

        $this->assertSame($artist, $result);
        $this->assertSame('New Name', $artist->getName());
    }

    public function testUpdatesArtistSources(): void
    {
        $oldSource = new ArtistSource();
        $artist = $this->makeArtist();
        $artist->addArtistSource($oldSource);

        $this->mockRepo($artist);
        $this->validator->method('validate')->willReturn(new ConstraintViolationList());

        $input = new ArtistUpdateInput();
        $input->artistSources = [new ArtistSourceDto('spotify', 'new123')];

        $this->processor->process($input, new Patch(), ['id' => 1]);

        $this->assertCount(1, $artist->getArtistSources());
    }

    public function testSkipsNullFields(): void
    {
        $artist = $this->makeArtist();
        $this->mockRepo($artist);
        $this->validator->method('validate')->willReturn(new ConstraintViolationList());

        $input = new ArtistUpdateInput();

        $this->processor->process($input, new Patch(), ['id' => 1]);

        $this->assertSame('Old Name', $artist->getName());
        $this->assertNull($input->artistSources);
    }

    public function testThrowsWhenNoId(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->processor->process(new ArtistUpdateInput(), new Patch(), []);
    }

    public function testThrowsWhenArtistNotFound(): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn(null);
        $this->em->method('getRepository')->willReturn($repo);

        $this->expectException(NotFoundHttpException::class);
        $this->processor->process(new ArtistUpdateInput(), new Patch(), ['id' => 999]);
    }

    public function testThrowsOnValidationFailure(): void
    {
        $artist = $this->makeArtist();
        $this->mockRepo($artist);

        $violations = $this->createMock(\Symfony\Component\Validator\ConstraintViolationListInterface::class);
        $violations->method('count')->willReturn(1);
        $this->validator->method('validate')->willReturn($violations);

        $input = new ArtistUpdateInput();
        $input->name = '';

        $this->expectException(ValidationException::class);
        $this->processor->process($input, new Patch(), ['id' => 1]);
    }

    public function testReturnsDataWhenNotArtistUpdateInput(): void
    {
        $data = new \stdClass();
        $result = $this->processor->process($data, new Patch(), ['id' => 1]); // @phpstan-ignore argument.type
        $this->assertSame($data, $result);
    }

    private function makeArtist(): Artist
    {
        $artist = new Artist();
        $artist->setName('Old Name');

        return $artist;
    }

    private function mockRepo(Artist $artist): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn($artist);
        $this->em->method('getRepository')->willReturn($repo);
    }
}
