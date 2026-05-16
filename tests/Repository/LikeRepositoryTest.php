<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Enum\LikeableTypeEnum;
use App\Entity\Like;
use App\Factory\PostFactory;
use App\Factory\UserFactory;
use App\Repository\LikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class LikeRepositoryTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    private EntityManagerInterface $em;

    private LikeRepository $repo;

    protected function setUp(): void
    {
        $container = static::getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->repo = $container->get(LikeRepository::class);
    }

    public function testFindContentOwnerReturnsPostAuthor(): void
    {
        $post = PostFactory::createOne();
        $liker = UserFactory::createOne();

        $like = (new Like())
            ->setUser($liker)
            ->setEntityId($post->getId())
            ->setEntityClass(LikeableTypeEnum::Post)
        ;

        $this->em->persist($like);
        $this->em->flush();

        $owner = $this->repo->findContentOwner($like);

        $this->assertSame($post->getUser()->getId(), $owner?->getId());
    }

    public function testFindContentOwnerReturnsNullWhenEntityNotFound(): void
    {
        $liker = UserFactory::createOne();

        $like = (new Like())
            ->setUser($liker)
            ->setEntityId(99_999_999)
            ->setEntityClass(LikeableTypeEnum::Post)
        ;

        $this->em->persist($like);
        $this->em->flush();

        $this->assertNull($this->repo->findContentOwner($like));
    }
}
