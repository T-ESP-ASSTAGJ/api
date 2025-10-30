<?php

declare(strict_types=1);

namespace App\State\Auth;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Auth\AuthRequestInput;
use App\ApiResource\Auth\AuthRequestOutput;
use App\Entity\VerificationUser;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Random\RandomException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * @implements ProcessorInterface<AuthRequestInput, AuthRequestOutput>
 */
readonly class AuthRequestProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private string $environment,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param AuthRequestInput     $data
     * @param Operation|null       $operation
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @throws RandomException|TransportExceptionInterface
     */
    public function process(mixed $data, $operation = null, array $uriVariables = [], array $context = []): AuthRequestOutput
    {
        if (!$data instanceof AuthRequestInput) {
            throw new BadRequestHttpException('Invalid data provided.');
        }

        $rawCode = (string) random_int(100000, 999999);
        $hashedCode = password_hash($rawCode, PASSWORD_DEFAULT);
        $expiresAt = new \DateTime('+10 minutes');

        /** @var VerificationUser|null $existingVerificationUser */
        $existingVerificationUser = $this->entityManager->getRepository(VerificationUser::class)->findOneBy(['email' => $data->email]);

        try {
            if ($existingVerificationUser) {
                $existingVerificationUser->setCode($hashedCode);
                $existingVerificationUser->setExpiresAt($expiresAt);
                $this->entityManager->persist($existingVerificationUser);
            } else {
                $verificationUser = new VerificationUser();
                $verificationUser->setEmail($data->email);
                $verificationUser->setCode($hashedCode);
                $verificationUser->setExpiresAt($expiresAt);
                $this->entityManager->persist($verificationUser);
            }

            $this->entityManager->flush();
        } catch (\Exception $e) {
            throw new BadRequestHttpException('Could not process the request.'.$e->getMessage());
        }

        if ('dev' === $this->environment) {
            $this->logger->debug(sprintf('Verification code for %s is: %s', $data->email, $rawCode));
        } else {
            $email = (new Email())
                ->to($data->email)
                ->subject('Your verification code')
                ->text('Your verification code is : '.$rawCode);

            $this->mailer->send($email);
        }

        $output = new AuthRequestOutput();
        $output->message = 'If a matching email was found, a code has been sent.';

        return $output;
    }
}
