<?php

declare(strict_types=1);

namespace App\State\Follow;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Follow\FollowOutput;
use App\Entity\Follow as FollowEntity;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<null, FollowEntity|FollowOutput>
 */
final readonly class FollowProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security,
    ) {
    }

    /**
     * @param null                 $data
     * @param Operation|null       $operation
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function process($data, $operation, array $uriVariables = [], array $context = []): FollowOutput
    {
        $currentUser = $this->security->getUser();

        if (!$currentUser instanceof User) {
            throw new \RuntimeException('Authentication required');
        }

        $userIdToFollow = $uriVariables['id'] ?? null;

        if (null === $userIdToFollow) {
            throw new BadRequestHttpException('Missing user ID to follow.');
        }

        /** @var User|null $userToFollow */
        $userToFollow = $this->em->getRepository(User::class)->find($userIdToFollow);

        if (!$userToFollow) {
            throw new NotFoundHttpException('User not found.');
        }

        if ($currentUser->getId() === $userToFollow->getId()) {
            throw new BadRequestHttpException("You can't follow or unfollow yourself.");
        }

        $follow = $this->em->getRepository(FollowEntity::class)->findOneBy([
            'follower' => $currentUser,
            'followedUser' => $userToFollow,
        ]);

        $output = new FollowOutput();

        if ('follow' === $operation->getName()) {
            if ($follow) {
                throw new BadRequestHttpException('Already following this user.');
            }
            $newFollow = new FollowEntity();
            $newFollow->setFollower($currentUser);
            $newFollow->setFollowedUser($userToFollow);
            $newFollow->setCreatedAt();

            $this->em->persist($newFollow);
            $this->em->flush();
            $output->message = 'Successfully followed the user.';

            return $output;
        }

        if ('unfollow' === $operation->getName()) {
            if (!$follow) {
                throw new BadRequestHttpException('You are not following this user.');
            }
            $this->em->remove($follow);
            $this->em->flush();
            $output->message = 'Successfully unfollowed the user.';

            return $output;
        }

        throw new \RuntimeException('Unsupported operation.');
    }
}
