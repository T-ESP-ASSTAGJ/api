<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use App\Entity\Enum\MessageTypeEnum;
use App\Entity\Message;
use App\Factory\UserFactory;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class MessageRepositoryTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    private EntityManagerInterface $em;

    private MessageRepository $repo;

    protected function setUp(): void
    {
        $container = static::getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->repo = $container->get(MessageRepository::class);
    }

    public function testCountUnreadMessagesWithNoLastReadAt(): void
    {
        $reader = UserFactory::createOne();
        $author = UserFactory::createOne();

        $conversation = (new Conversation())->setIsGroup(false);
        $this->em->persist($conversation);

        $conversation->addParticipant((new ConversationParticipant())->setUser($reader));
        $conversation->addParticipant((new ConversationParticipant())->setUser($author));

        $msg1 = (new Message())->setConversation($conversation)->setAuthor($author)->setType(MessageTypeEnum::Text)->setContent('hello');
        $msg2 = (new Message())->setConversation($conversation)->setAuthor($author)->setType(MessageTypeEnum::Text)->setContent('world');
        $this->em->persist($msg1);
        $this->em->persist($msg2);
        $this->em->flush();

        $this->assertSame(2, $this->repo->countUnreadMessages($conversation, $reader, null));
    }

    public function testCountUnreadMessagesExcludesMessagesFromReader(): void
    {
        $reader = UserFactory::createOne();

        $conversation = (new Conversation())->setIsGroup(false);
        $this->em->persist($conversation);
        $conversation->addParticipant((new ConversationParticipant())->setUser($reader));

        $ownMsg = (new Message())->setConversation($conversation)->setAuthor($reader)->setType(MessageTypeEnum::Text)->setContent('my own');
        $this->em->persist($ownMsg);
        $this->em->flush();

        $this->assertSame(0, $this->repo->countUnreadMessages($conversation, $reader, null));
    }

    public function testCountUnreadMessagesWithLastReadAtFiltersOlderMessages(): void
    {
        $reader = UserFactory::createOne();
        $author = UserFactory::createOne();

        $conversation = (new Conversation())->setIsGroup(false);
        $this->em->persist($conversation);
        $conversation->addParticipant((new ConversationParticipant())->setUser($reader));
        $conversation->addParticipant((new ConversationParticipant())->setUser($author));

        $msg = (new Message())->setConversation($conversation)->setAuthor($author)->setType(MessageTypeEnum::Text)->setContent('hi');
        $this->em->persist($msg);
        $this->em->flush();

        // lastReadAt in the future → message was created before it → 0 unread
        $future = new \DateTimeImmutable('+1 hour');
        $this->assertSame(0, $this->repo->countUnreadMessages($conversation, $reader, $future));

        // lastReadAt in the past → message was created after it → 1 unread
        $past = new \DateTimeImmutable('-1 hour');
        $this->assertSame(1, $this->repo->countUnreadMessages($conversation, $reader, $past));
    }
}
