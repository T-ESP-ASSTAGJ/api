<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use App\Factory\UserFactory;
use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ConversationRepositoryTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    private EntityManagerInterface $em;

    private ConversationRepository $repo;

    protected function setUp(): void
    {
        $container = static::getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->repo = $container->get(ConversationRepository::class);
    }

    public function testFindPrivateConversationReturnsConversationWhenBothUsersAreParticipants(): void
    {
        $userA = UserFactory::createOne();
        $userB = UserFactory::createOne();

        $conversation = (new Conversation())->setIsGroup(false);
        $this->em->persist($conversation);

        $p1 = (new ConversationParticipant())->setUser($userA);
        $p2 = (new ConversationParticipant())->setUser($userB);
        $conversation->addParticipant($p1);
        $conversation->addParticipant($p2);

        $this->em->flush();

        $result = $this->repo->findPrivateConversation($userA, $userB);

        $this->assertSame($conversation->getId(), $result?->getId());
    }

    public function testFindPrivateConversationReturnsNullWhenNoSharedConversation(): void
    {
        $userA = UserFactory::createOne();
        $userB = UserFactory::createOne();

        $result = $this->repo->findPrivateConversation($userA, $userB);

        $this->assertNull($result);
    }

    public function testFindByUserReturnsActiveParticipations(): void
    {
        $user = UserFactory::createOne();

        $conversation = (new Conversation())->setIsGroup(false);
        $this->em->persist($conversation);

        $participant = (new ConversationParticipant())->setUser($user);
        $conversation->addParticipant($participant);

        $this->em->flush();

        $results = $this->repo->findByUser($user);

        $this->assertCount(1, $results);
        $this->assertSame($conversation->getId(), $results[0]->getId());
    }

    public function testFindByUserExcludesConversationsWhereUserLeft(): void
    {
        $user = UserFactory::createOne();

        $conversation = (new Conversation())->setIsGroup(false);
        $this->em->persist($conversation);

        $participant = (new ConversationParticipant())
            ->setUser($user)
            ->setLeftAt(new \DateTimeImmutable())
        ;
        $conversation->addParticipant($participant);

        $this->em->flush();

        $results = $this->repo->findByUser($user);

        $this->assertCount(0, $results);
    }
}
