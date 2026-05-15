<?php

declare(strict_types=1);

namespace App\Tests\State\Track;

use ApiPlatform\Metadata\Patch;
use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\Track\TrackInput;
use App\Entity\Track;
use App\State\Track\TrackUpdateProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TrackUpdateProcessorTest extends TestCase
{
    /**
     * @var EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private EntityManagerInterface $em;

    /**
     * @var ValidatorInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private ValidatorInterface $validator;

    private TrackUpdateProcessor $processor;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->processor = new TrackUpdateProcessor($this->em, $this->validator);
    }

    public function testUpdatesTrack(): void
    {
        $track = new Track();
        $this->mockRepo($track);
        $this->validator->method('validate')->willReturn(new ConstraintViolationList());
        $this->em->expects($this->once())->method('flush');

        $input = new TrackInput('new-id', 'New Title', 'New Artist', 2021);
        $result = $this->processor->process($input, new Patch(), ['id' => 1]);

        $this->assertSame($track, $result);
        $this->assertSame('new-id', $track->getSongId());
        $this->assertSame('New Title', $track->getTitle());
    }

    public function testThrowsWhenNotTrackInput(): void
    {
        $this->expectException(UnprocessableEntityHttpException::class);
        $this->processor->process(new \stdClass(), new Patch(), ['id' => 1]); // @phpstan-ignore argument.type
    }

    public function testThrowsWhenNoId(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->processor->process(new TrackInput('x', 'x', 'x', null), new Patch(), []);
    }

    public function testThrowsWhenTrackNotFound(): void
    {
        $this->mockRepo(null);
        $this->expectException(NotFoundHttpException::class);
        $this->processor->process(new TrackInput('x', 'x', 'x', null), new Patch(), ['id' => 999]);
    }

    public function testThrowsOnValidationFailure(): void
    {
        $track = new Track();
        $this->mockRepo($track);

        $violations = $this->createMock(\Symfony\Component\Validator\ConstraintViolationListInterface::class);
        $violations->method('count')->willReturn(1);
        $this->validator->method('validate')->willReturn($violations);

        $this->expectException(ValidationException::class);
        $this->processor->process(new TrackInput('', '', '', null), new Patch(), ['id' => 1]);
    }

    private function mockRepo(?Track $track): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn($track);
        $this->em->method('getRepository')->willReturn($repo);
    }
}
