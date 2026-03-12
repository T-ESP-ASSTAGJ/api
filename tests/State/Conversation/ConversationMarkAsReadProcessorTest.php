<?php

declare(strict_types=1);

namespace App\Tests\State\Conversation;

use App\Entity\User;
use App\Factory\ConversationFactory;
use App\Factory\ConversationParticipantFactory;
use App\Factory\UserFactory;
use App\State\Conversation\ConversationMarkAsReadProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Zenstruck\Foundry\Test\ResetDatabase;
use App\Tests\Trait\AuthenticationTrait;

class ConversationMarkAsReadProcessorTest extends KernelTestCase
{
    use ResetDatabase;
    use AuthenticationTrait;

    private ConversationMarkAsReadProcessor $processor;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->processor = self::getContainer()->get(ConversationMarkAsReadProcessor::class);
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testMarkConversationAsReadResetsUnreadCount(): void
    {
        $user = UserFactory::createOne();
        $conversation = ConversationFactory::new()->privateConversation()->create();
        $participant = ConversationParticipantFactory::new()->create([
            'conversation' => $conversation,
            'user' => $user,
        ]);

        $participant->object()->incrementUnreadCount();
        $participant->object()->incrementUnreadCount();
        $participant->object()->incrementUnreadCount();
        $this->em->flush();

        $this->em->refresh($participant->object());
        $this->assertSame(3, $participant->getUnreadCount());

        $this->authenticateUser($user);

        $this->processor->process($conversation->object());

        $this->em->refresh($participant->object());
        $this->assertSame(0, $participant->getUnreadCount());
    }

    public function testMarkConversationAsReadForGroupConversation(): void
    {
        $user1 = UserFactory::createOne();
        $user2 = UserFactory::createOne();
        $conversation = ConversationFactory::new()->groupConversation('Test Group')->create();

        $participant1 = ConversationParticipantFactory::new()->create([
            'conversation' => $conversation,
            'user' => $user1,
        ]);
        $participant2 = ConversationParticipantFactory::new()->create([
            'conversation' => $conversation,
            'user' => $user2,
        ]);

        $participant1->object()->incrementUnreadCount();
        $participant1->object()->incrementUnreadCount();
        $participant2->object()->incrementUnreadCount();
        $this->em->flush();

        $this->em->refresh($participant1->object());
        $this->em->refresh($participant2->object());
        $this->assertSame(2, $participant1->getUnreadCount());
        $this->assertSame(1, $participant2->getUnreadCount());

        $this->authenticateUser($user1);

        $this->processor->process($conversation->object());

        $this->em->refresh($participant1->object());
        $this->em->refresh($participant2->object());
        $this->assertSame(0, $participant1->getUnreadCount());
        $this->assertSame(1, $participant2->getUnreadCount());
    }

    public function testMarkAsReadWhenNotParticipantThrowsAccessDenied(): void
    {
        $user = UserFactory::createOne();
        $otherUser = UserFactory::createOne();
        $conversation = ConversationFactory::new()->privateConversation()->create();

        ConversationParticipantFactory::new()->create([
            'conversation' => $conversation,
            'user' => $otherUser,
        ]);

        $this->authenticateUser($user);

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('You are not a participant of this conversation');

        $this->processor->process($conversation->object());
    }

    public function testMarkAsReadWhenInactiveParticipantThrowsAccessDenied(): void
    {
        $user = UserFactory::createOne();
        $conversation = ConversationFactory::new()->privateConversation()->create();

        ConversationParticipantFactory::new()
            ->inactive()
            ->create([
                'conversation' => $conversation,
                'user' => $user,
            ]);

        $this->authenticateUser($user);

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('You are not a participant of this conversation');

        $this->processor->process($conversation->object());
    }

    public function testMarkAsReadWithoutAuthenticationThrowsUnauthorized(): void
    {
        $conversation = ConversationFactory::new()->privateConversation()->create();

        $this->expectException(UnauthorizedHttpException::class);
        $this->expectExceptionMessage('Authentication required');

        $this->processor->process($conversation->_get());
    }

    public function testMarkAsReadWithZeroUnreadCountRemainsZero(): void
    {
        $user = UserFactory::createOne();
        $conversation = ConversationFactory::new()->privateConversation()->create();
        $participant = ConversationParticipantFactory::new()->create([
            'conversation' => $conversation,
            'user' => $user,
        ]);

        $this->em->refresh($participant->object());
        $this->assertSame(0, $participant->getUnreadCount());

        $this->authenticateUser($user);

        $this->processor->process($conversation->object());

        $this->em->refresh($participant->object());
        $this->assertSame(0, $participant->getUnreadCount());
    }

}
