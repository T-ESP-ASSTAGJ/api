<?php

declare(strict_types=1);

namespace App\Tests\State\Auth;

use App\ApiResource\Auth\AuthRequestInput;
use App\Entity\VerificationUser;
use App\State\Auth\AuthRequestProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;

class AuthRequestProcessorUnitTest extends TestCase
{
    public function testLogsAndThrowsOnDatabaseError(): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        $mockEm = $this->createMock(EntityManagerInterface::class);
        $mockEm->method('getRepository')->with(VerificationUser::class)->willReturn($repo);
        $mockEm->method('flush')->willThrowException(new \Exception('Database error'));

        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockLogger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Could not process auth request: Database error'))
        ;

        $mockMailer = $this->createMock(MailerInterface::class);

        $processor = new AuthRequestProcessor($mockEm, $mockMailer, $mockLogger);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not process the request.');

        $processor->process(new AuthRequestInput('error@example.com'));
    }
}
