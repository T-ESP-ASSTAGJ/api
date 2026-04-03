<?php

declare(strict_types=1);

namespace App\Tests\State\Report;

use App\ApiResource\Report\ReportCreateInput;
use App\Entity\Enum\ReportableTypeEnum;
use App\Entity\Enum\ReportReasonEnum;
use App\Entity\Report;
use App\Factory\PostFactory;
use App\Factory\UserFactory;
use App\State\Report\ReportCreateProcessor;
use App\Tests\Trait\AuthenticationTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Zenstruck\Foundry\Test\ResetDatabase;

class ReportCreateProcessorTest extends KernelTestCase
{
    use ResetDatabase;
    use AuthenticationTrait;

    private ReportCreateProcessor $processor;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->processor = self::getContainer()->get(ReportCreateProcessor::class);
    }

    public function testCreateReportOnPost(): void
    {
        $user = UserFactory::createOne();
        $post = PostFactory::createOne();

        $this->authenticateUser($user);

        $input = new ReportCreateInput();
        $input->entityClass = ReportableTypeEnum::Post;
        $input->entityId = $post->getId();
        $input->reason = ReportReasonEnum::Spam;

        $this->processor->process($input, $this->createMock(\ApiPlatform\Metadata\Operation::class));

        $em = self::getContainer()->get('doctrine')->getManager();
        $report = $em->getRepository(Report::class)->findOneBy([
            'entityId' => $post->getId(),
            'entityClass' => ReportableTypeEnum::Post,
        ]);

        $this->assertNotNull($report);
        $this->assertSame(ReportReasonEnum::Spam, $report->getReason());
        $this->assertNull($report->getMessage());
    }

    public function testCreateReportWithMessage(): void
    {
        $user = UserFactory::createOne();
        $post = PostFactory::createOne();

        $this->authenticateUser($user);

        $input = new ReportCreateInput();
        $input->entityClass = ReportableTypeEnum::Post;
        $input->entityId = $post->getId();
        $input->reason = ReportReasonEnum::Other;
        $input->message = 'Ce contenu est inapproprié.';

        $this->processor->process($input, $this->createMock(\ApiPlatform\Metadata\Operation::class));

        $em = self::getContainer()->get('doctrine')->getManager();
        $report = $em->getRepository(Report::class)->findOneBy([
            'entityId' => $post->getId(),
        ]);

        $this->assertNotNull($report);
        $this->assertSame('Ce contenu est inapproprié.', $report->getMessage());
    }

    public function testDuplicateReportThrowsBadRequestException(): void
    {
        $user = UserFactory::createOne();
        $post = PostFactory::createOne();

        $this->authenticateUser($user);

        $input = new ReportCreateInput();
        $input->entityClass = ReportableTypeEnum::Post;
        $input->entityId = $post->getId();
        $input->reason = ReportReasonEnum::Spam;

        $this->processor->process($input, $this->createMock(\ApiPlatform\Metadata\Operation::class));

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Vous avez déjà signalé ce contenu.');

        $this->processor->process($input, $this->createMock(\ApiPlatform\Metadata\Operation::class));
    }

    public function testReportOnNonExistentEntityThrowsNotFoundException(): void
    {
        $user = UserFactory::createOne();

        $this->authenticateUser($user);

        $input = new ReportCreateInput();
        $input->entityClass = ReportableTypeEnum::Post;
        $input->entityId = 999999;
        $input->reason = ReportReasonEnum::Spam;

        $this->expectException(NotFoundHttpException::class);

        $this->processor->process($input, $this->createMock(\ApiPlatform\Metadata\Operation::class));
    }

    public function testUnauthenticatedUserThrowsUnauthorizedException(): void
    {
        UserFactory::createOne();
        $post = PostFactory::createOne();

        $input = new ReportCreateInput();
        $input->entityClass = ReportableTypeEnum::Post;
        $input->entityId = $post->getId();
        $input->reason = ReportReasonEnum::Spam;

        $this->expectException(UnauthorizedHttpException::class);

        $this->processor->process($input, $this->createMock(\ApiPlatform\Metadata\Operation::class));
    }

    public function testCreateReportOnUser(): void
    {
        $reporter = UserFactory::createOne();
        $reportedUser = UserFactory::createOne();

        $this->authenticateUser($reporter);

        $input = new ReportCreateInput();
        $input->entityClass = ReportableTypeEnum::User;
        $input->entityId = $reportedUser->getId();
        $input->reason = ReportReasonEnum::Harassment;

        $this->processor->process($input, $this->createMock(\ApiPlatform\Metadata\Operation::class));

        $em = self::getContainer()->get('doctrine')->getManager();
        $report = $em->getRepository(Report::class)->findOneBy([
            'entityId' => $reportedUser->getId(),
            'entityClass' => ReportableTypeEnum::User,
        ]);

        $this->assertNotNull($report);
        $this->assertSame(ReportReasonEnum::Harassment, $report->getReason());
    }
}
