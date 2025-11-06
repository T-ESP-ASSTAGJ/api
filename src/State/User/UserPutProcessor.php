<?php

declare(strict_types=1);

namespace App\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\User\UserPutInput;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements ProcessorInterface<UserPutInput, User>
 */
class UserPutProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private Security $security,
    ) {
    }

    /**
     * @param UserPutInput $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): User
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        if (!$user) {
            throw new AccessDeniedHttpException('You must be authenticated to update your profile.');
        }

        try {
            $user->setUsername($data->username);
            if ($data->phoneNumber) {
                $user->setPhoneNumber($data->phoneNumber);
            }

            $user->setProfilePicture($data->profilePicture);
            $user->setBio($data->bio);

            $violations = $this->validator->validate($user);
            if ($violations->count() > 0) {
                throw new ValidationException($violations);
            }

            $this->entityManager->flush();
        } catch (\Throwable $exception) {
            throw new \RuntimeException('An error occurred while updating the user profile: '.$exception->getMessage());
        }

        return $user;
    }
}
