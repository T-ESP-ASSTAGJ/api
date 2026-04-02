<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    use SearchQueryTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(\sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findDeviceTokenByUser(int $userId): ?string
    {
        $result = $this->createQueryBuilder('u')
            ->select('u.deviceToken')
            ->where('u.id = :userId')
            ->andWhere('u.deviceToken IS NOT NULL')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $result['deviceToken'] ?? null;
    }

    /** @return \App\Entity\User[] */
    public function searchByQuery(string $query, int $offset, int $limit): array
    {
        return $this->buildSearchQuery('username', $query, $offset, $limit, 'username', 'ASC')->getResult();
    }

    public function deleteTokenByUserToken(string $deviceToken): void
    {
        $this->createQueryBuilder('u')
            ->update()
            ->set('u.deviceToken', ':null')
            ->where('u.deviceToken = :deviceToken')
            ->setParameter('null', null)
            ->setParameter('deviceToken', $deviceToken)
            ->getQuery()
            ->execute()
        ;
    }
}
