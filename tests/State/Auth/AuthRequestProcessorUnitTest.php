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
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class AuthRequestProcessorUnitTest extends TestCase
{
    public function testSendsTemplatedEmailOnSuccess(): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        $mockEm = $this->createMock(EntityManagerInterface::class);
        $mockEm->method('getRepository')->with(VerificationUser::class)->willReturn($repo);

        $mockMailer = $this->createMock(MailerInterface::class);
        $mockMailer->expects($this->once())
            ->method('send')
            ->with($this->callback(function (mixed $email): bool {
                return $email instanceof TemplatedEmail
                    && 'emails/verification_code.html.twig' === $email->getHtmlTemplate()
                    && isset($email->getContext()['code']);
            }))
        ;

        $mockLogger = $this->createMock(LoggerInterface::class);

        $processor = new AuthRequestProcessor($mockEm, $mockMailer, $mockLogger);

        $output = $processor->process(new AuthRequestInput('user@example.com'));

        $this->assertSame('If a matching email was found, a code has been sent.', $output->message);
    }

    public function testPropagatesDatabaseError(): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        $mockEm = $this->createMock(EntityManagerInterface::class);
        $mockEm->method('getRepository')->with(VerificationUser::class)->willReturn($repo);
        $mockEm->method('flush')->willThrowException(new \Exception('Database error'));

        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockLogger->expects($this->never())->method('error');

        $mockMailer = $this->createMock(MailerInterface::class);
        $mockMailer->expects($this->never())->method('send');

        $processor = new AuthRequestProcessor($mockEm, $mockMailer, $mockLogger);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database error');

        $processor->process(new AuthRequestInput('error@example.com'));
    }
}
