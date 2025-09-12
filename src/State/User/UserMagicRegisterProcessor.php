<?php

declare(strict_types=1);

namespace App\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\DTO\User\UserGetOutput;
use App\DTO\User\UserMagicRegisterInput;
use App\DTO\User\UserMagicRegisterOutput;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements ProcessorInterface<UserMagicRegisterInput, UserGetOutput>
 */
final readonly class UserMagicRegisterProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
        private ObjectMapperInterface $objectMapper,
        private JWTTokenManagerInterface $jwtManager,
    ) {
    }

    /**
     * @param UserMagicRegisterInput $data
     * @param Operation|null         $operation
     * @param array<string, mixed>   $uriVariables
     * @param array<string, mixed>   $context
     *
     * @return UserGetOutput
     */
    public function process(mixed $data, $operation = null, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof UserMagicRegisterInput) {
            return $data;
        }

        /** @var User $existingUser */
        $existingUser = $this->em->getRepository(User::class)->findOneBy(['email' => $data->email]);

        if ($existingUser) {
            $token = $this->jwtManager->create($existingUser);

            return $this->objectMapper->map(
                (object) [
                    'username' => $existingUser->getUsername(),
                    'email' => $existingUser->getEmail(),
                    'token' => $token,
                ],
                UserMagicRegisterOutput::class
            );
        }

        if (!$data->username || !$data->email) {
            return throw new ValidationException('Missing required fields.');
        }

        $user = new User();
        $user->setUsername($data->username);
        $user->setEmail($data->email);
        $user->setPhoneNumber($data->phoneNumber);
        $user->setProfilePicture($data->profilePicture);
        $user->setRoles(['ROLE_USER']);

        $violations = $this->validator->validate($user);

        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $this->em->persist($user);
        $this->em->flush();

        $token = $this->jwtManager->create($user);

        return $this->objectMapper->map(
            (object) [
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'token' => $token,
            ],
            UserMagicRegisterOutput::class
        );
    }
}
