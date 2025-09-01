<?php

declare(strict_types=1);

namespace App\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\DTO\User\UserCreateInput;
use App\DTO\User\UserGetOutput;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements ProcessorInterface<UserCreateInput, UserGetOutput>
 */
final readonly class UserCreateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    /**
     * @param UserCreateInput      $data
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return UserGetOutput
     */
    public function process(mixed $data, ?Operation $operation = null, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof UserCreateInput) {
            $user = new User();
            $user->setUsername($data->username);
            $user->setEmail($data->email);
            $user->setPassword($this->passwordHasher->hashPassword($user, $data->password));
            $user->setRoles(['ROLE_USER']);
            $user->setBio($data->bio);
            $user->setProfilePicture($data->profilePicture);
            $user->setIsConfirmed(false);

            $violations = $this->validator->validate($user);
            if ($violations->count() > 0) {
                throw new ValidationException($violations);
            }

            $this->em->persist($user);
            $this->em->flush();

            return $this->objectMapper->map($user, UserGetOutput::class);
        }

        return $data;
    }
}
