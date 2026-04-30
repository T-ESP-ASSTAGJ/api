<?php

declare(strict_types=1);

namespace App\Tests\State\Auth;

use ApiPlatform\Symfony\Bundle\Test\Client;
use App\ApiResource\Auth\AuthVerificationInput;
use App\Entity\User;
use App\Entity\VerificationUser;
use App\State\Auth\AuthVerifyProcessor;
use ApiPlatform\Validator\Exception\ValidationException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityRepository;
use App\Factory\UserFactory;
use App\Factory\VerificationUserFactory;
use App\Tests\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AuthVerifyProcessorTest extends ApiTestCase
{
    public function testCreatesNewUserAndReturnsToken(): void
    {
        $rawCode = '123456';

        VerificationUserFactory::createOne([
            'email' => 'newuser@example.com',
            'code' => password_hash($rawCode, \PASSWORD_DEFAULT),
            'expiresAt' => new \DateTime('+10 minutes'),
        ]);

        $this->request([
            'email' => 'newuser@example.com',
            'code' => $rawCode,
        ]);

        self::assertResponseStatusCodeSame(201);
        self::assertJsonContains([
            'isVerified' => true,
            'needsProfile' => true,
            'message' => 'Verification successful.',
        ]);

        $data = json_decode(static::getClient()->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $data);
        $this->assertNotEmpty($data['token']);

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'newuser@example.com']);
        $this->assertNotNull($user);
        $this->assertTrue($user->getIsVerified());
        $this->assertTrue($user->getNeedsProfile());

        $this->assertNull(
            $this->em->getRepository(VerificationUser::class)->findOneBy(['email' => 'newuser@example.com']),
        );
    }

    public function testVerifiesExistingUser(): void
    {
        $rawCode = '654321';

        UserFactory::createOne([
            'email' => 'existing@example.com',
            'needsProfile' => false,
            'isVerified' => false,
        ]);

        VerificationUserFactory::createOne([
            'email' => 'existing@example.com',
            'code' => password_hash($rawCode, \PASSWORD_DEFAULT),
            'expiresAt' => new \DateTime('+10 minutes'),
        ]);

        $this->request([
            'email' => 'existing@example.com',
            'code' => $rawCode,
        ]);

        self::assertResponseStatusCodeSame(201);
        self::assertJsonContains([
            'isVerified' => true,
            'needsProfile' => false,
        ]);

        $this->em->clear();
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'existing@example.com']);
        $this->assertTrue($user->getIsVerified());
    }

    public function testReturns403OnWrongCode(): void
    {
        VerificationUserFactory::createOne([
            'email' => 'user@example.com',
            'code' => password_hash('123456', \PASSWORD_DEFAULT),
            'expiresAt' => new \DateTime('+10 minutes'),
        ]);

        $this->request([
            'email' => 'user@example.com',
            'code' => '000000',
        ]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testReturns403OnExpiredCode(): void
    {
        $rawCode = '123456';

        VerificationUserFactory::createOne([
            'email' => 'user@example.com',
            'code' => password_hash($rawCode, \PASSWORD_DEFAULT),
            'expiresAt' => new \DateTime('-1 minute'),
        ]);

        $this->request([
            'email' => 'user@example.com',
            'code' => $rawCode,
        ]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testReturns403WhenNoVerificationUserExists(): void
    {
        $this->request([
            'email' => 'ghost@example.com',
            'code' => '123456',
        ]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testReturns422OnMissingFields(): void
    {
        $this->request([]);

        self::assertResponseStatusCodeSame(422);
    }

    public function testProcessThrowsValidationException(): void
    {
        $email = 'test@example.com';
        $code = '123456';

        $verificationUser = new VerificationUser();
        $verificationUser->setCode(password_hash($code, \PASSWORD_DEFAULT));
        $verificationUser->setExpiresAt(new \DateTime('+10 minutes'));

        $mockRepoVerification = $this->createMock(EntityRepository::class);
        $mockRepoUser = $this->createMock(EntityRepository::class);

        $mockRepoVerification->method('findOneBy')->willReturn($verificationUser);
        $mockRepoUser->method('findOneBy')->willReturn(null);

        $mockEm = $this->createMock(EntityManagerInterface::class);
        $mockValidator = $this->createMock(ValidatorInterface::class);

        $mockEm->method('getRepository')->willReturnMap([
            [VerificationUser::class, $mockRepoVerification],
            [User::class, $mockRepoUser],
        ]);

        $violations = new ConstraintViolationList([
            $this->createMock(ConstraintViolationInterface::class)
        ]);
        $mockValidator->method('validate')->willReturn($violations);

        $processor = new AuthVerifyProcessor(
            $mockEm,
            $mockValidator,
            $this->createMock(JWTTokenManagerInterface::class)
        );

        $this->expectException(ValidationException::class);

        $input = new AuthVerificationInput($email, $code);
        $processor->process($input);
    }

    public function testProcessThrowsRuntimeExceptionOnDatabaseError(): void
    {
        $email = 'test@example.com';
        $code = '123456';

        $verificationUser = new VerificationUser();
        $verificationUser->setCode(password_hash($code, \PASSWORD_DEFAULT));
        $verificationUser->setExpiresAt(new \DateTime('+10 minutes'));

        $mockRepoVerification = $this->createMock(EntityRepository::class);
        $mockRepoUser = $this->createMock(EntityRepository::class);

        $mockRepoVerification->method('findOneBy')->willReturn($verificationUser);
        $mockRepoUser->method('findOneBy')->willReturn(new User());

        $mockEm = $this->createMock(EntityManagerInterface::class);
        $mockValidator = $this->createMock(ValidatorInterface::class);

        $mockEm->method('getRepository')->willReturnMap([
            [VerificationUser::class, $mockRepoVerification],
            [User::class, $mockRepoUser],
        ]);

        $mockValidator->method('validate')->willReturn(new ConstraintViolationList());

        $mockEm->method('flush')->willThrowException(new \Exception('Database connection lost'));

        $processor = new AuthVerifyProcessor(
            $mockEm,
            $mockValidator,
            $this->createMock(JWTTokenManagerInterface::class)
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to verify user: Database connection lost');

        $input = new AuthVerificationInput($email, $code);
        $processor->process($input);
    }

    private function request(array $json): ResponseInterface
    {
        return static::createClient()->request('POST', '/api/auth/verify', [
            'json' => $json,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }
}
