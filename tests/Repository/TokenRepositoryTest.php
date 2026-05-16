<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Token;
use App\Factory\UserFactory;
use App\Repository\TokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class TokenRepositoryTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    private EntityManagerInterface $em;

    private TokenRepository $repo;

    protected function setUp(): void
    {
        $container = static::getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->repo = $container->get(TokenRepository::class);
    }

    public function testFindByUserAndPlatformReturnsMatchingToken(): void
    {
        $token = $this->createToken(Token::PLATFORM_SPOTIFY);

        $result = $this->repo->findByUserAndPlatform($token->getUser(), Token::PLATFORM_SPOTIFY);

        $this->assertSame($token->getId(), $result?->getId());
    }

    public function testFindByUserAndPlatformReturnsNullWhenNoneMatch(): void
    {
        $token = $this->createToken(Token::PLATFORM_SPOTIFY);

        $result = $this->repo->findByUserAndPlatform($token->getUser(), Token::PLATFORM_DEEZER);

        $this->assertNull($result);
    }

    public function testFindAllByUserReturnsAllTokensForUser(): void
    {
        $user = UserFactory::createOne();

        $t1 = (new Token())->setUser($user)->setPlatform(Token::PLATFORM_SPOTIFY)->setAccessToken('t1')->setExpiresAt(new \DateTime('+1 hour'))->setPlatformUserId('u1');
        $t2 = (new Token())->setUser($user)->setPlatform(Token::PLATFORM_DEEZER)->setAccessToken('t2')->setExpiresAt(new \DateTime('+1 hour'))->setPlatformUserId('u2');
        $this->em->persist($t1);
        $this->em->persist($t2);
        $this->em->flush();

        $results = $this->repo->findAllByUser($user);

        $this->assertCount(2, $results);
    }

    public function testFindAllByPlatformReturnsTokensForPlatform(): void
    {
        $this->createToken(Token::PLATFORM_SPOTIFY);
        $this->createToken(Token::PLATFORM_SPOTIFY);
        $this->createToken(Token::PLATFORM_DEEZER);

        $results = $this->repo->findAllByPlatform(Token::PLATFORM_SPOTIFY);

        $this->assertCount(2, $results);
        foreach ($results as $token) {
            $this->assertSame(Token::PLATFORM_SPOTIFY, $token->getPlatform());
        }
    }

    public function testFindExpiredTokensReturnsOnlyExpiredTokens(): void
    {
        $expired = $this->createToken(Token::PLATFORM_SPOTIFY, new \DateTime('-1 hour'));
        $this->createToken(Token::PLATFORM_DEEZER, new \DateTime('+1 hour'));

        $results = $this->repo->findExpiredTokens();

        $this->assertCount(1, $results);
        $this->assertSame($expired->getId(), $results[0]->getId());
    }

    private function createToken(string $platform = Token::PLATFORM_SPOTIFY, \DateTimeInterface $expiresAt = new \DateTime('+1 hour')): Token
    {
        $token = (new Token())
            ->setUser(UserFactory::createOne())
            ->setPlatform($platform)
            ->setAccessToken('access_'.uniqid())
            ->setExpiresAt($expiresAt)
            ->setPlatformUserId('platform_user_'.uniqid())
        ;

        $this->em->persist($token);
        $this->em->flush();

        return $token;
    }
}
