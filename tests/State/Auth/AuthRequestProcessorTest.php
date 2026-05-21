<?php

declare(strict_types=1);

namespace App\Tests\State\Auth;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\VerificationUser;
use App\Factory\VerificationUserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\EventListener\MessageLoggerListener;
use Symfony\Component\Mime\Email;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class AuthRequestProcessorTest extends ApiTestCase
{
    use Factories;
    use ResetDatabase;

    protected static ?bool $alwaysBootKernel = true;

    private Client $client;

    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        static::getContainer()
            ->get('mailer.message_logger_listener')
            ->reset()
        ;
    }

    public function testInvalidInput(): void
    {
        $this->client->request('POST', '/api/auth/request', [
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
        $this->client->request('POST', '/api/auth/request', [
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
        $this->assertSame('Your Jamly verification code', $sentEmail->getSubject());
        $this->assertStringContainsString('Verify your identity', $sentEmail->getHtmlBody());
    }

    public function testUpdatesExistingVerificationUser(): void
    {
        $existing = VerificationUserFactory::createOne([
            'email' => 'existing@example.com',
            'code' => password_hash('111111', \PASSWORD_DEFAULT),
            'expiresAt' => new \DateTime('+1 minute'),
        ]);

        $oldCode = $existing->getCode();

        $this->client->request('POST', '/api/auth/request', [
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
        $this->client->request('POST', '/api/auth/request', [
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
        $this->client->request('POST', '/api/auth/request', [
            'json' => ['email' => 'not-an-email'],
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        self::assertResponseStatusCodeSame(422);
    }

    private function getMessageLoggerListener(): MessageLoggerListener
    {
        return static::getContainer()->get('mailer.message_logger_listener');
    }
}
