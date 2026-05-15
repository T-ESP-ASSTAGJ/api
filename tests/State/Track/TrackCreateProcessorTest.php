<?php

declare(strict_types=1);

namespace App\Tests\State\Track;

use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\Track\TrackInput;
use App\Entity\Track;
use App\State\Track\TrackCreateProcessor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TrackCreateProcessorTest extends TestCase
{
    /**
     * @var EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private EntityManagerInterface $em;

    /**
     * @var ValidatorInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private ValidatorInterface $validator;

    private TrackCreateProcessor $processor;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->processor = new TrackCreateProcessor($this->em, $this->validator);
    }

    public function testCreatesTrack(): void
    {
        $this->validator->method('validate')->willReturn(new ConstraintViolationList());
        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $input = new TrackInput('song-123', 'Blinding Lights', 'The Weeknd', 2020);

        $result = $this->processor->process($input);

        $this->assertInstanceOf(Track::class, $result);
        $this->assertSame('song-123', $result->getSongId());
        $this->assertSame('Blinding Lights', $result->getTitle());
        $this->assertSame('The Weeknd', $result->getArtistName());
        $this->assertSame(2020, $result->getReleaseYear());
    }

    public function testThrowsOnValidationFailure(): void
    {
        $violations = $this->createMock(\Symfony\Component\Validator\ConstraintViolationListInterface::class);
        $violations->method('count')->willReturn(1);
        $this->validator->method('validate')->willReturn($violations);

        $this->expectException(ValidationException::class);
        $this->processor->process(new TrackInput('', '', '', null));
    }

    public function testReturnsDataWhenNotTrackInput(): void
    {
        $data = new \stdClass();
        $result = $this->processor->process($data); // @phpstan-ignore argument.type
        $this->assertSame($data, $result);
    }
}
