<?php

declare(strict_types=1);

namespace App\Repository;

use App\ApiResource\User\UserFollowOutput;
use App\Entity\Follow;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Follow>
 */
class FollowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Follow::class);
    }

    /**
     * @return UserFollowOutput[]
     */
    public function findFollowers(int $userId): array
    {
        $result = $this->createQueryBuilder('f')
            ->select('follower.id', 'follower.username', 'follower.profilePicture')
            ->join('f.follower', 'follower')
            ->where('f.followedUser = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();

        return array_map(
            static fn (array $data) => new UserFollowOutput(
                $data['id'],
                $data['username'],
                $data['profilePicture']
            ),
            $result,
        );
    }

    /**
     * @return UserFollowOutput[]
     */
    public function findFollowing(int $userId): array
    {
        $result = $this->createQueryBuilder('f')
            ->select('followed.id', 'followed.username', 'followed.profilePicture')
            ->join('f.followedUser', 'followed')
            ->where('f.follower = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();

        return array_map(
            static fn (array $data) => new UserFollowOutput(
                $data['id'],
                $data['username'],
                $data['profilePicture']
            ),
            $result,
        );
    }
}
