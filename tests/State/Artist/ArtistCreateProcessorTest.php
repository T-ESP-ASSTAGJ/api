<?php

declare(strict_types=1);

namespace App\Tests\State\Artist;

use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\Artist\ArtistCreateInput;
use App\ApiResource\Artist\ArtistSourceDto;
use App\Entity\Artist;
use App\State\Artist\ArtistCreateProcessor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ArtistCreateProcessorTest extends TestCase
{
    /**
     * @var EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private EntityManagerInterface $em;

    /**
     * @var ValidatorInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private ValidatorInterface $validator;

    private ArtistCreateProcessor $processor;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->processor = new ArtistCreateProcessor($this->em, $this->validator);
    }

    public function testCreatesArtistWithoutSources(): void
    {
        $this->validator->method('validate')->willReturn(new ConstraintViolationList());
        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $input = new ArtistCreateInput();
        $input->name = 'Test Artist';

        $result = $this->processor->process($input);

        $this->assertInstanceOf(Artist::class, $result);
        $this->assertSame('Test Artist', $result->getName());
    }

    public function testCreatesArtistWithSources(): void
    {
        $this->validator->method('validate')->willReturn(new ConstraintViolationList());
        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $input = new ArtistCreateInput();
        $input->name = 'Pink Floyd';
        $input->artistSources = [new ArtistSourceDto('spotify', 'abc123')];

        $result = $this->processor->process($input);

        $this->assertInstanceOf(Artist::class, $result);
        $this->assertCount(1, $result->getArtistSources());
    }

    public function testThrowsOnValidationFailure(): void
    {
        $violations = $this->createMock(\Symfony\Component\Validator\ConstraintViolationListInterface::class);
        $violations->method('count')->willReturn(1);
        $this->validator->method('validate')->willReturn($violations);

        $input = new ArtistCreateInput();
        $input->name = '';

        $this->expectException(ValidationException::class);
        $this->processor->process($input);
    }

    public function testReturnsDataAsIsWhenNotArtistCreateInput(): void
    {
        $data = new \stdClass();
        $result = $this->processor->process($data); // @phpstan-ignore argument.type

        $this->assertSame($data, $result);
    }
}
