<?php

declare(strict_types=1);

namespace App\Tests\State\Post;

use ApiPlatform\Metadata\Post as PostOperation;
use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\Post\PostCreateInput;
use App\ApiResource\Track\TrackInput;
use App\Entity\Post;
use App\Entity\Track;
use App\Entity\User;
use App\Service\ImageService;
use App\Service\Track\TrackService;
use App\State\Post\PostCreateProcessor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PostCreateProcessorTest extends TestCase
{
    /**
     * @var EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private EntityManagerInterface $em;

    /**
     * @var ValidatorInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private ValidatorInterface $validator;

    /**
     * @var Security&\PHPUnit\Framework\MockObject\MockObject
     */
    private Security $security;

    /**
     * @var ImageService&\PHPUnit\Framework\MockObject\MockObject
     */
    private ImageService $imageService;

    /**
     * @var TrackService&\PHPUnit\Framework\MockObject\MockObject
     */
    private TrackService $trackService;

    private PostCreateProcessor $processor;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->imageService = $this->createMock(ImageService::class);
        $this->trackService = $this->createMock(TrackService::class);
        $this->processor = new PostCreateProcessor(
            $this->em,
            $this->validator,
            $this->security,
            $this->imageService,
            $this->trackService,
        );
    }

    public function testReturnsDataWhenNotPostCreateInput(): void
    {
        $data = new \stdClass();
        $result = $this->processor->process($data, new PostOperation()); // @phpstan-ignore argument.type
        $this->assertSame($data, $result);
    }

    public function testThrowsWhenUnauthenticated(): void
    {
        $this->security->method('getUser')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->processor->process($this->makeInput(), new PostOperation());
    }

    public function testCreatesPost(): void
    {
        $user = new User();
        $track = new Track();

        $this->security->method('getUser')->willReturn($user);
        $this->trackService->method('findOrCreate')->willReturn($track);
        $this->imageService->method('saveBase64ToStorage')
            ->willReturnOnConsecutiveCalls('/uploads/posts/front.jpg', '/uploads/posts/back.jpg')
        ;
        $this->validator->method('validate')->willReturn(new ConstraintViolationList());
        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $result = $this->processor->process($this->makeInput(), new PostOperation());

        $this->assertInstanceOf(Post::class, $result);
        $this->assertSame($user, $result->getUser());
        $this->assertSame($track, $result->getTrack());
        $this->assertSame('Hello world', $result->getCaption());
        $this->assertSame('/uploads/posts/front.jpg', $result->getFrontImage());
        $this->assertSame('/uploads/posts/back.jpg', $result->getBackImage());
        $this->assertSame('Paris', $result->getLocation());
    }

    public function testThrowsOnValidationFailure(): void
    {
        $user = new User();
        $track = new Track();

        $this->security->method('getUser')->willReturn($user);
        $this->trackService->method('findOrCreate')->willReturn($track);
        $this->imageService->method('saveBase64ToStorage')->willReturn('/uploads/posts/img.jpg');

        $violations = $this->createMock(\Symfony\Component\Validator\ConstraintViolationListInterface::class);
        $violations->method('count')->willReturn(1);
        $this->validator->method('validate')->willReturn($violations);

        $this->expectException(ValidationException::class);
        $this->processor->process($this->makeInput(), new PostOperation());
    }

    private function makeInput(): PostCreateInput
    {
        return new PostCreateInput(
            track: new TrackInput('song-1', 'Test Song', 'Test Artist', 2024),
            caption: 'Hello world',
            frontImage: 'data:image/jpeg;base64,abc',
            backImage: 'data:image/jpeg;base64,def',
            location: 'Paris',
        );
    }
}
