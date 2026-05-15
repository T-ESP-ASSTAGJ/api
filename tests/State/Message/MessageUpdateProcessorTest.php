<?php

declare(strict_types=1);

namespace App\Tests\State\Message;

use ApiPlatform\Metadata\Patch;
use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\Message\MessageUpdateInput;
use App\Entity\Message;
use App\Entity\User;
use App\Service\Message\MessageMercurePublisherService;
use App\State\Message\MessageUpdateProcessor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MessageUpdateProcessorTest extends TestCase
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
     * @var MessageMercurePublisherService&\PHPUnit\Framework\MockObject\MockObject
     */
    private MessageMercurePublisherService $mercurePublisher;

    private MessageUpdateProcessor $processor;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->mercurePublisher = $this->createMock(MessageMercurePublisherService::class);
        $this->processor = new MessageUpdateProcessor(
            $this->em,
            $this->validator,
            $this->security,
            $this->mercurePublisher,
        );
    }

    public function testThrowsWhenMessageNotInContext(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->processor->process(new MessageUpdateInput(), new Patch(), [], []);
    }

    public function testThrowsWhenUnauthenticated(): void
    {
        $message = new Message();
        $this->security->method('getUser')->willReturn(null);

        $this->expectException(UnauthorizedHttpException::class);
        $this->processor->process(new MessageUpdateInput(), new Patch(), [], ['previous_data' => $message]);
    }

    public function testThrowsOnValidationFailure(): void
    {
        $message = new Message();
        $user = new User();
        $this->security->method('getUser')->willReturn($user);

        $violations = $this->createMock(\Symfony\Component\Validator\ConstraintViolationListInterface::class);
        $violations->method('count')->willReturn(1);
        $this->validator->method('validate')->willReturn($violations);

        $input = new MessageUpdateInput();
        $input->content = 'new content';

        $this->expectException(ValidationException::class);
        $this->processor->process($input, new Patch(), [], ['previous_data' => $message]);
    }

    public function testUpdatesMessageContent(): void
    {
        $message = new Message();
        $user = new User();
        $this->security->method('getUser')->willReturn($user);
        $this->validator->method('validate')->willReturn(new ConstraintViolationList());
        $this->em->expects($this->once())->method('flush');
        $this->mercurePublisher->expects($this->once())->method('publish')->with($message);

        $input = new MessageUpdateInput();
        $input->content = 'Updated content';

        $result = $this->processor->process($input, new Patch(), [], ['previous_data' => $message]);

        $this->assertSame($message, $result);
        $this->assertSame('Updated content', $message->getContent());
    }

    public function testDoesNotUpdateContentWhenNull(): void
    {
        $message = new Message();
        $message->setContent('original');
        $user = new User();
        $this->security->method('getUser')->willReturn($user);
        $this->validator->method('validate')->willReturn(new ConstraintViolationList());
        $this->em->expects($this->once())->method('flush');

        $input = new MessageUpdateInput();
        // content is null

        $result = $this->processor->process($input, new Patch(), [], ['previous_data' => $message]);

        $this->assertSame('original', $message->getContent());
    }
}
