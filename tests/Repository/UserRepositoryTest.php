<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\User;
use App\Factory\UserFactory;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserRepositoryTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    private EntityManagerInterface $em;

    private UserRepository $repo;

    protected function setUp(): void
    {
        $container = static::getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->repo = $container->get(UserRepository::class);
    }

    public function testUpgradePasswordUpdatesUserPassword(): void
    {
        $user = UserFactory::createOne();

        $this->repo->upgradePassword($user, 'new_hashed_password');

        $this->em->clear();
        $updated = $this->em->find(User::class, $user->getId());

        $this->assertSame('new_hashed_password', $updated->getPassword());
    }

    public function testUpgradePasswordThrowsForNonUserInstance(): void
    {
        $notAUser = new class implements PasswordAuthenticatedUserInterface {
            public function getPassword(): string
            {
                return 'old';
            }
        };

        $this->expectException(UnsupportedUserException::class);

        $this->repo->upgradePassword($notAUser, 'new_password');
    }

    public function testFindDeviceTokenByUserReturnsTokenWhenSet(): void
    {
        $user = UserFactory::createOne();
        $user->setDeviceToken('my-device-token');
        $this->em->flush();

        $result = $this->repo->findDeviceTokenByUser($user->getId());

        $this->assertSame('my-device-token', $result);
    }

    public function testFindDeviceTokenByUserReturnsNullWhenNotSet(): void
    {
        $user = UserFactory::createOne();

        $result = $this->repo->findDeviceTokenByUser($user->getId());

        $this->assertNull($result);
    }

    public function testFindByUsernameReturnsMatchingUsers(): void
    {
        UserFactory::createOne(['username' => 'john_doe_unique']);
        UserFactory::createOne(['username' => 'jane_smith']);

        $results = $this->repo->findByUsername('john_doe', 0, 10);

        $this->assertCount(1, $results);
        $this->assertSame('john_doe_unique', $results[0]->getUsername());
    }

    public function testDeleteTokenByUserTokenClearsDeviceToken(): void
    {
        $user = UserFactory::createOne();
        $user->setDeviceToken('token-to-delete');
        $this->em->flush();

        $this->repo->deleteTokenByUserToken('token-to-delete');

        $this->em->clear();
        $updated = $this->em->find(User::class, $user->getId());
        $this->assertNull($updated->getDeviceToken());
    }
}
