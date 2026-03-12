<?php

declare(strict_types=1);

namespace App\Tests\State\Conversation;

use ApiPlatform\Metadata\GetCollection;
use App\Entity\User;
use App\Factory\ConversationFactory;
use App\Factory\ConversationParticipantFactory;
use App\Factory\MessageFactory;
use App\Factory\UserFactory;
use App\State\Conversation\ConversationListProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Zenstruck\Foundry\Test\ResetDatabase;
use App\Tests\Trait\AuthenticationTrait;

class ConversationListProviderTest extends KernelTestCase
{
    use ResetDatabase;
    use AuthenticationTrait;

    private ConversationListProvider $provider;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->provider = self::getContainer()->get(ConversationListProvider::class);
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testProvideReturnsUserActiveConversations(): void
    {
        $user = UserFactory::createOne();
        $otherUser = UserFactory::createOne();

        $conversation1 = ConversationFactory::new()->privateConversation()->create();
        $conversation2 = ConversationFactory::new()->groupConversation('Group 1')->create();
        $conversation3 = ConversationFactory::new()->privateConversation()->create();

        ConversationParticipantFactory::new()->create(['conversation' => $conversation1, 'user' => $user]);
        ConversationParticipantFactory::new()->create(['conversation' => $conversation1, 'user' => $otherUser]);

        ConversationParticipantFactory::new()->create(['conversation' => $conversation2, 'user' => $user]);
        ConversationParticipantFactory::new()->create(['conversation' => $conversation2, 'user' => $otherUser]);

        ConversationParticipantFactory::new()->create(['conversation' => $conversation3, 'user' => $otherUser]);

        $this->authenticateUser($user);

        $result = $this->provider->provide(new GetCollection());

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function testProvideReturnsConversationsOrderedByUpdatedAt(): void
    {
        $user = UserFactory::createOne();

        $conversation1 = ConversationFactory::new()->privateConversation()->create();
        sleep(1);
        $conversation2 = ConversationFactory::new()->privateConversation()->create();
        sleep(1);
        $conversation3 = ConversationFactory::new()->privateConversation()->create();

        ConversationParticipantFactory::new()->create(['conversation' => $conversation1, 'user' => $user]);
        ConversationParticipantFactory::new()->create(['conversation' => $conversation2, 'user' => $user]);
        ConversationParticipantFactory::new()->create(['conversation' => $conversation3, 'user' => $user]);

        $this->authenticateUser($user);

        $result = $this->provider->provide(new GetCollection());

        $this->assertCount(3, $result);
        $this->assertSame($conversation3->getId(), $result[0]->getId());
        $this->assertSame($conversation2->getId(), $result[1]->getId());
        $this->assertSame($conversation1->getId(), $result[2]->getId());
    }

    public function testProvideExcludesConversationsUserHasLeft(): void
    {
        $user = UserFactory::createOne();

        $activeConversation = ConversationFactory::new()->privateConversation()->create();
        $leftConversation = ConversationFactory::new()->privateConversation()->create();

        ConversationParticipantFactory::new()->create([
            'conversation' => $activeConversation,
            'user' => $user,
        ]);

        ConversationParticipantFactory::new()
            ->inactive()
            ->create([
                'conversation' => $leftConversation,
                'user' => $user,
            ]);

        $this->authenticateUser($user);

        $result = $this->provider->provide(new GetCollection());

        $this->assertCount(1, $result);
        $this->assertSame($activeConversation->getId(), $result[0]->getId());
    }

    public function testProvideIncludesUnreadCountForEachConversation(): void
    {
        $user = UserFactory::createOne();
        $otherUser = UserFactory::createOne();

        $conversation = ConversationFactory::new()->privateConversation()->create();

        $userParticipant = ConversationParticipantFactory::new()->create([
            'conversation' => $conversation,
            'user' => $user,
        ]);

        ConversationParticipantFactory::new()->create([
            'conversation' => $conversation,
            'user' => $otherUser,
        ]);

        MessageFactory::new()->create([
            'conversation' => $conversation,
            'author' => $otherUser,
            'content' => 'Message 1',
        ]);

        MessageFactory::new()->create([
            'conversation' => $conversation,
            'author' => $otherUser,
            'content' => 'Message 2',
        ]);

        $this->em->refresh($userParticipant->object());
        $this->assertSame(2, $userParticipant->getUnreadCount());

        $this->authenticateUser($user);

        $result = $this->provider->provide(new GetCollection());

        $this->assertCount(1, $result);
        $this->assertSame(2, $result[0]->getUnreadCount());
    }

    public function testProvideWithMultipleConversationsShowsCorrectUnreadCounts(): void
    {
        $user = UserFactory::createOne();
        $user1 = UserFactory::createOne();
        $user2 = UserFactory::createOne();

        $conversation1 = ConversationFactory::new()->privateConversation()->create();
        $conversation2 = ConversationFactory::new()->privateConversation()->create();

        $participant1 = ConversationParticipantFactory::new()->create([
            'conversation' => $conversation1,
            'user' => $user,
        ]);
        ConversationParticipantFactory::new()->create([
            'conversation' => $conversation1,
            'user' => $user1,
        ]);

        $participant2 = ConversationParticipantFactory::new()->create([
            'conversation' => $conversation2,
            'user' => $user,
        ]);
        ConversationParticipantFactory::new()->create([
            'conversation' => $conversation2,
            'user' => $user2,
        ]);

        MessageFactory::new()->create([
            'conversation' => $conversation1,
            'author' => $user1,
            'content' => 'Message in conv1',
        ]);

        MessageFactory::new()->create([
            'conversation' => $conversation2,
            'author' => $user2,
            'content' => 'Message 1 in conv2',
        ]);
        MessageFactory::new()->create([
            'conversation' => $conversation2,
            'author' => $user2,
            'content' => 'Message 2 in conv2',
        ]);
        MessageFactory::new()->create([
            'conversation' => $conversation2,
            'author' => $user2,
            'content' => 'Message 3 in conv2',
        ]);

        $this->em->refresh($participant1->object());
        $this->em->refresh($participant2->object());

        $this->authenticateUser($user);

        $result = $this->provider->provide(new GetCollection());

        $this->assertCount(2, $result);

        $conv1Result = null;
        $conv2Result = null;
        foreach ($result as $conv) {
            if ($conv->getId() === $conversation1->getId()) {
                $conv1Result = $conv;
            }
            if ($conv->getId() === $conversation2->getId()) {
                $conv2Result = $conv;
            }
        }

        $this->assertNotNull($conv1Result);
        $this->assertNotNull($conv2Result);
        $this->assertSame(1, $conv1Result->getUnreadCount());
        $this->assertSame(3, $conv2Result->getUnreadCount());
    }

    public function testProvideReturnsEmptyArrayForUserWithNoConversations(): void
    {
        $user = UserFactory::createOne();

        $this->authenticateUser($user);

        $result = $this->provider->provide(new GetCollection());

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testProvideWithoutAuthenticationThrowsUnauthorized(): void
    {
        $this->expectException(UnauthorizedHttpException::class);
        $this->expectExceptionMessage('Authentication required');

        $this->provider->provide(new GetCollection());
    }

    public function testProvideReturnsConversationsWithZeroUnreadCount(): void
    {
        $user = UserFactory::createOne();
        $otherUser = UserFactory::createOne();

        $conversation = ConversationFactory::new()->privateConversation()->create();

        $userParticipant = ConversationParticipantFactory::new()->create([
            'conversation' => $conversation,
            'user' => $user,
        ]);
        ConversationParticipantFactory::new()->create([
            'conversation' => $conversation,
            'user' => $otherUser,
        ]);

        $this->em->refresh($userParticipant->object());
        $this->assertSame(0, $userParticipant->getUnreadCount());

        $this->authenticateUser($user);

        $result = $this->provider->provide(new GetCollection());

        $this->assertCount(1, $result);
        $this->assertSame(0, $result[0]->getUnreadCount());
    }

}
