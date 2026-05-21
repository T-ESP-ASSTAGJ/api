<?php

declare(strict_types=1);

namespace App\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\User\UserPatchInput;
use App\Entity\User;
use App\Service\ImageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements ProcessorInterface<UserPatchInput, User>
 */
class UserPatchProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private Security $security,
        private ImageService $imageService,
    ) {
    }

    /**
     * @param UserPatchInput $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): User
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new AccessDeniedHttpException('You must be authenticated.');
        }

        if (isset($data->username)) {
            $user->setUsername($data->username);
        }
        if (isset($data->phoneNumber)) {
            $user->setPhoneNumber($data->phoneNumber);
        }
        if (isset($data->bio)) {
            $user->setBio($data->bio);
        }
        if (isset($data->profilePicture)) {
            $profilePicture = $this->imageService->saveBase64ToStorage($data->profilePicture, 'profile');
            $user->setProfilePicture($profilePicture);
        }

        $violations = $this->validator->validate($user);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $this->entityManager->flush();

        return $user;
    }
}
