<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Token;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Token>
 */
class TokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Token::class);
    }

    public function findByUserAndPlatform(User $user, string $platform): ?Token
    {
        return $this->findOneBy([
            'user' => $user,
            'platform' => $platform,
        ]);
    }

    /** @return Token[] */
    public function findAllByUser(User $user): array
    {
        return $this->findBy(['user' => $user]);
    }

    /** @return Token[] */
    public function findAllByPlatform(string $platform): array
    {
        return $this->findBy(['platform' => $platform]);
    }

    /** @return Token[] */
    public function findExpiredTokens(): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.expiresAt < :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();
    }
}
