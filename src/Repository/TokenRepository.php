<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Token;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class TokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Token::class);
    }

    public function save(Token $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Token $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByUserAndPlatform(User $user, string $platform): ?Token
    {
        return $this->findOneBy([
            'user' => $user,
            'platform' => $platform,
        ]);
    }

    public function findAllByUser(User $user): array
    {
        return $this->findBy(['user' => $user]);
    }

    public function findAllByPlatform(string $platform): array
    {
        return $this->findBy(['platform' => $platform]);
    }

    public function findExpiredTokens(): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.expiresAt < :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();
    }
}