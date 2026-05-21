<?php

declare(strict_types=1);

namespace App\Tests\State\User;

use ApiPlatform\Metadata\Patch;
use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\User\UserPatchInput;
use App\Entity\User;
use App\Service\ImageService;
use App\State\User\UserPatchProcessor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserPatchProcessorTest extends TestCase
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

    private UserPatchProcessor $processor;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->imageService = $this->createMock(ImageService::class);
        $this->processor = new UserPatchProcessor($this->em, $this->validator, $this->security, $this->imageService);
    }

    public function testPatchesUserFields(): void
    {
        $user = new User();
        $this->security->method('getUser')->willReturn($user);
        $this->validator->method('validate')->willReturn(new ConstraintViolationList());
        $this->em->expects($this->once())->method('flush');

        $input = new UserPatchInput();
        $input->username = 'newname';
        $input->bio = 'My bio';

        $result = $this->processor->process($input, new Patch());

        $this->assertSame($user, $result);
        $this->assertSame('newname', $user->getUsername());
        $this->assertSame('My bio', $user->getBio());
    }

    public function testPatchesPhoneNumber(): void
    {
        $user = new User();
        $this->security->method('getUser')->willReturn($user);
        $this->validator->method('validate')->willReturn(new ConstraintViolationList());
        $this->em->expects($this->once())->method('flush');

        $input = new UserPatchInput();
        $input->phoneNumber = '+33612345678';

        $this->processor->process($input, new Patch());

        $this->assertSame('+33612345678', $user->getPhoneNumber());
    }

    public function testPatchesProfilePictureViaImageService(): void
    {
        $user = new User();
        $this->security->method('getUser')->willReturn($user);
        $this->validator->method('validate')->willReturn(new ConstraintViolationList());
        $this->em->expects($this->once())->method('flush');

        $this->imageService
            ->expects($this->once())
            ->method('saveBase64ToStorage')
            ->with('data:image/png;base64,abc==', 'profile')
            ->willReturn('/uploads/profile/abc.png')
        ;

        $input = new UserPatchInput();
        $input->profilePicture = 'data:image/png;base64,abc==';

        $this->processor->process($input, new Patch());

        $this->assertSame('/uploads/profile/abc.png', $user->getProfilePicture());
    }

    public function testImageServiceNotCalledWhenNoPicture(): void
    {
        $user = new User();
        $this->security->method('getUser')->willReturn($user);
        $this->validator->method('validate')->willReturn(new ConstraintViolationList());

        $this->imageService->expects($this->never())->method('saveBase64ToStorage');

        $input = new UserPatchInput();
        $input->username = 'nochange';

        $this->processor->process($input, new Patch());
    }

    public function testThrowsWhenUnauthenticated(): void
    {
        $this->security->method('getUser')->willReturn(null);

        $this->expectException(AccessDeniedHttpException::class);
        $this->processor->process(new UserPatchInput(), new Patch());
    }

    public function testThrowsOnValidationFailure(): void
    {
        $user = new User();
        $this->security->method('getUser')->willReturn($user);

        $violations = $this->createMock(\Symfony\Component\Validator\ConstraintViolationListInterface::class);
        $violations->method('count')->willReturn(1);
        $this->validator->method('validate')->willReturn($violations);

        $this->expectException(ValidationException::class);
        $this->processor->process(new UserPatchInput(), new Patch());
    }
}
