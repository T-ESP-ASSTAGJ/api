<?php

declare(strict_types=1);

namespace App\Tests\State\Report;

use ApiPlatform\Metadata\Post as PostOperation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Report\ReportCreateInput;
use App\Entity\Enum\ReportableTypeEnum;
use App\Entity\Enum\ReportReasonEnum;
use App\Entity\Post as PostEntity;
use App\Entity\User;
use App\State\Report\ReportCreateProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ReportCreateProcessorTest extends TestCase
{
    /**
     * @var ProcessorInterface<mixed, mixed>&\PHPUnit\Framework\MockObject\MockObject
     */
    private ProcessorInterface $persistProcessor;

    /**
     * @var EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private EntityManagerInterface $em;

    /**
     * @var Security&\PHPUnit\Framework\MockObject\MockObject
     */
    private Security $security;

    private ReportCreateProcessor $processor;

    protected function setUp(): void
    {
        $this->persistProcessor = $this->createMock(ProcessorInterface::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->processor = new ReportCreateProcessor(
            $this->persistProcessor,
            $this->em,
            $this->security,
        );
    }

    public function testSkipsNonReportCreateInput(): void
    {
        $this->persistProcessor->expects($this->never())->method('process');
        $this->processor->process(new \stdClass()); // @phpstan-ignore argument.type
    }

    public function testThrowsWhenEntityNotFound(): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn(null);
        $this->em->method('getRepository')->willReturn($repo);

        $this->expectException(NotFoundHttpException::class);
        $this->processor->process($this->makeInput(), new PostOperation());
    }

    public function testThrowsWhenUnauthenticated(): void
    {
        $post = new PostEntity();
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn($post);
        $this->em->method('getRepository')->willReturn($repo);
        $this->security->method('getUser')->willReturn(null);

        $this->expectException(UnauthorizedHttpException::class);
        $this->processor->process($this->makeInput(), new PostOperation());
    }

    public function testCreatesReport(): void
    {
        $user = new User();
        $post = new PostEntity();

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn($post);
        $this->em->method('getRepository')->willReturn($repo);
        $this->security->method('getUser')->willReturn($user);
        $this->persistProcessor->expects($this->once())->method('process');

        $this->processor->process($this->makeInput(), new PostOperation());
    }

    public function testThrowsOnDuplicateReport(): void
    {
        $user = new User();
        $post = new PostEntity();

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn($post);
        $this->em->method('getRepository')->willReturn($repo);
        $this->security->method('getUser')->willReturn($user);
        $this->persistProcessor->method('process')->willThrowException(new \Exception('Duplicate'));

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->processor->process($this->makeInput(), new PostOperation());
    }

    private function makeInput(): ReportCreateInput
    {
        $input = new ReportCreateInput();
        $input->entityClass = ReportableTypeEnum::Post;
        $input->entityId = 1;
        $input->reason = ReportReasonEnum::Spam;
        $input->message = 'This is spam';

        return $input;
    }
}
