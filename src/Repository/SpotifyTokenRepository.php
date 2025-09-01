<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SpotifyToken;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SpotifyToken>
 */
class SpotifyTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SpotifyToken::class);
    }

    public function findByUser(User $user): ?SpotifyToken
    {
        return $this->findOneBy(['user' => $user]);
    }

    public function findValidTokenByUser(User $user): ?SpotifyToken
    {
        $token = $this->findByUser($user);
        
        if ($token && !$token->isExpired()) {
            return $token;
        }
        
        return null;
    }

    public function save(SpotifyToken $token): void
    {
        $this->getEntityManager()->persist($token);
        $this->getEntityManager()->flush();
    }

    public function remove(SpotifyToken $token): void
    {
        $this->getEntityManager()->remove($token);
        $this->getEntityManager()->flush();
    }
}
