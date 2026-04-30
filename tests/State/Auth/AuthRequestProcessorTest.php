<?php

declare(strict_types=1);

namespace App\Tests\State\Auth;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\VerificationUser;
use App\Factory\VerificationUserFactory;
use App\ApiResource\Auth\AuthRequestInput;
use App\State\Auth\AuthRequestProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\EventListener\MessageLoggerListener;
use Symfony\Component\Mime\Email;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class AuthRequestProcessorTest extends ApiTestCase
{
    use Factories;
    use ResetDatabase;

    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::$alwaysBootKernel = true;
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        static::getContainer()
            ->get('mailer.message_logger_listener')
            ->reset()
        ;
    }

    public function testInvalidInput(): void
    {
        static::createClient()->request('POST', '/api/auth/request', [
            'json' => ['toto' => 'toto'],
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        self::assertResponseStatusCodeSame(400);
    }

    public function testCreatesVerificationUserAndSendsEmail(): void
    {
        static::createClient()->request('POST', '/api/auth/request', [
            'json' => ['email' => 'test@example.com'],
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/json; charset=utf-8');
        self::assertJsonContains([
            'message' => 'If a matching email was found, a code has been sent.',
        ]);

        $verificationUser = $this->em->getRepository(VerificationUser::class)
            ->findOneBy(['email' => 'test@example.com'])
        ;

        $this->assertNotNull($verificationUser);
        $this->assertNotEmpty($verificationUser->getCode());
        $this->assertGreaterThan(new \DateTime(), $verificationUser->getExpiresAt());

        $mailerEvents = $this->getMessageLoggerListener();
        $this->assertCount(1, $mailerEvents->getEvents()->getMessages());

        $sentEmail = $mailerEvents->getEvents()->getMessages()[0];
        $this->assertInstanceOf(Email::class, $sentEmail);

        $this->assertSame('test@example.com', $sentEmail->getTo()[0]->getAddress());
        $this->assertSame('Your verification code', $sentEmail->getSubject());
        $this->assertStringContainsString('Your verification code is :', $sentEmail->getTextBody());
    }

    public function testUpdatesExistingVerificationUser(): void
    {
        $existing = VerificationUserFactory::createOne([
            'email' => 'existing@example.com',
            'code' => password_hash('111111', \PASSWORD_DEFAULT),
            'expiresAt' => new \DateTime('+1 minute'),
        ]);

        $oldCode = $existing->getCode();

        static::createClient()->request('POST', '/api/auth/request', [
            'json' => ['email' => 'existing@example.com'],
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        self::assertResponseStatusCodeSame(201);

        $refreshed = $this->em->getRepository(VerificationUser::class)
            ->findOneBy(['email' => 'existing@example.com'])
        ;

        $this->assertNotSame($oldCode, $refreshed->getCode());
        $this->assertCount(1, self::getMailerMessages());
    }

    public function testReturnsBadRequestOnMissingEmail(): void
    {
        static::createClient()->request('POST', '/api/auth/request', [
            'json' => [],
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        self::assertResponseStatusCodeSame(400);
    }

    public function testReturnsBadRequestOnInvalidEmail(): void
    {
        static::createClient()->request('POST', '/api/auth/request', [
            'json' => ['email' => 'not-an-email'],
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        self::assertResponseStatusCodeSame(422);
    }

    public function testHandlesExceptionDuringDatabaseOperation(): void
    {
        $mockEm = $this->createMock(EntityManagerInterface::class);
        $mockEm->method('getRepository')->willReturn($this->em->getRepository(VerificationUser::class));
        $mockEm->method('persist')->willReturn(null);
        $mockEm->method('flush')->willThrowException(new \Exception('Database error'));

        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockLogger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Could not process auth request: Database error'));

        $processor = new AuthRequestProcessor(
            $mockEm,
            static::getContainer()->get(MailerInterface::class),
            $mockLogger
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not process the request.');

        $processor->process(new AuthRequestInput('error@example.com'));
    }


    private function getMessageLoggerListener(): MessageLoggerListener
    {
        return static::getContainer()->get('mailer.message_logger_listener');
    }
}
