<?php

declare(strict_types=1);

namespace App\State\Follow;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Auth\AuthRequestInput;
use App\ApiResource\Follow\FollowInput;
use App\Entity\Follow as FollowEntity;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class FollowProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security,
    ) {
    }

    /**
     * @param AuthRequestInput     $data
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): FollowEntity|FollowInput|null
    {
        $currentUser = $this->security->getUser();

        if (!$currentUser instanceof User) {
            throw new \RuntimeException('Authentication required');
        }

        $userIdToFollow = $uriVariables['id'] ?? null;

        if (null === $userIdToFollow) {
            throw new BadRequestHttpException('Missing user ID to follow.');
        }

        $userToFollow = $this->em->getRepository(User::class)->find($userIdToFollow);

        if (!$userToFollow) {
            throw new NotFoundHttpException('User not found.');
        }

        if ($currentUser->getId() === $userToFollow->getId()) {
            throw new BadRequestHttpException("You can't follow or unfollow yourself.");
        }

        $follow = $this->em->getRepository(FollowEntity::class)->findOneBy([
            'follower' => $currentUser,
            'followed' => $userToFollow,
        ]);

        if ('follow' === $operation->getName()) {
            if ($follow) {
                throw new \RuntimeException('Already following this user.');
            }
            $newFollow = new FollowEntity();
            $newFollow->setFollower($currentUser);
            $newFollow->setFollowed($userToFollow);
            $newFollow->setCreatedAt();

            $this->em->persist($newFollow);
            $this->em->flush();

            return $newFollow;
        }

        if ('unfollow' === $operation->getName()) {
            if (!$follow) {
                throw new \RuntimeException('You are not following this user.');
            }
            $this->em->remove($follow);
            $this->em->flush();

            return null;
        }

        return $data;
    }
}
