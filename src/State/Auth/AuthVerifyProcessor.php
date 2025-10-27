<?php

declare(strict_types=1);

namespace App\State\Auth;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\Auth\AuthVerificationInput;
use App\ApiResource\Auth\AuthVerificationOutput;
use App\Entity\User;
use App\Entity\VerificationUser;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements ProcessorInterface<AuthVerificationInput, AuthVerificationOutput>
 */
readonly class AuthVerifyProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private JWTTokenManagerInterface $jwtManager,
    ) {
    }

    /**
     * @param AuthVerificationInput $data
     * @param Operation|null        $operation
     * @param array<string, mixed>  $uriVariables
     * @param array<string, mixed>  $context
     */
    public function process(mixed $data, $operation = null, array $uriVariables = [], array $context = []): AuthVerificationOutput
    {
        if (!$data instanceof AuthVerificationInput) {
            throw new BadRequestHttpException('Invalid data provided.');
        }

        $verificationUser = $this->entityManager
            ->getRepository(VerificationUser::class)
            ->findOneBy(['email' => $data->email]);

        if (
            !$verificationUser
            || !password_verify($data->code, $verificationUser->getCode())
            || $verificationUser->getExpiresAt() < new \DateTime()
        ) {
            throw new AccessDeniedHttpException('Invalid credentials.');
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data->email]);

        try {
            if (!$user) {
                $user = new User();
                $user->setEmail($data->email);
                $user->setNeedsProfile(true);
                $this->entityManager->persist($user);
            }

            $user->setIsVerified(true);

            $violations = $this->validator->validate($user);
            if ($violations->count() > 0) {
                throw new ValidationException($violations);
            }

            $this->entityManager->remove($verificationUser);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to create or update user: '.$e->getMessage());
        }

        $token = $this->jwtManager->create($user);

        $output = new AuthVerificationOutput();
        $output->isVerified = true;
        $output->message = 'Verification successful.';
        $output->needsProfile = $user->getNeedsProfile();
        $output->token = $token;

        return $output;
    }
}
