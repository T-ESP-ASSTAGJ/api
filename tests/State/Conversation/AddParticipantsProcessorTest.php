<?php

declare(strict_types=1);

namespace App\Tests\State\Conversation;

use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\Conversation\AddParticipantsInput;
use App\Entity\ConversationParticipant;
use App\Entity\User;
use App\Factory\ConversationFactory;
use App\Factory\ConversationParticipantFactory;
use App\Factory\UserFactory;
use App\State\Conversation\AddParticipantsProcessor;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Zenstruck\Foundry\Test\ResetDatabase;
use App\Tests\Trait\AuthenticationTrait;

class AddParticipantsProcessorTest extends KernelTestCase
{
    use ResetDatabase;
    use AuthenticationTrait;

    private AddParticipantsProcessor $processor;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->processor = self::getContainer()->get(AddParticipantsProcessor::class);
    }

    public function testAddParticipantsToGroupConversationAsAdmin(): void
    {
        $admin = UserFactory::createOne();
        $conversation = ConversationFactory::new()->groupConversation('Test Group')->create();
        ConversationParticipantFactory::new()
            ->admin()
            ->create(['conversation' => $conversation, 'user' => $admin]);

        $newUser1 = UserFactory::createOne();
        $newUser2 = UserFactory::createOne();

        $this->authenticateUser($admin);

        $input = new AddParticipantsInput(
            userIds: [$newUser1->getId(), $newUser2->getId()]
        );

        $result = $this->processor->process(
            $input,
            null,
            ['id' => $conversation->getId()],
            []
        );

        $this->assertCount(3, $result->getActiveParticipants());
        $this->assertTrue($this->hasParticipantWithUser($result->getParticipants()->toArray(), $newUser1->getId()));
        $this->assertTrue($this->hasParticipantWithUser($result->getParticipants()->toArray(), $newUser2->getId()));
    }

    public function testAddParticipantsAsNonAdminThrowsAccessDenied(): void
    {
        $member = UserFactory::createOne();
        $conversation = ConversationFactory::new()->groupConversation('Test Group')->create();
        ConversationParticipantFactory::new()
            ->member()
            ->create(['conversation' => $conversation, 'user' => $member]);

        $newUser = UserFactory::createOne();

        $this->authenticateUser($member);

        $input = new AddParticipantsInput(
            userIds: [$newUser->getId()]
        );

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('Seuls les administrateurs du groupe peuvent ajouter des participants');

        $this->processor->process(
            $input,
            null,
            ['id' => $conversation->getId()],
            []
        );
    }

    public function testAddParticipantsToPrivateConversationThrowsBadRequest(): void
    {
        $user1 = UserFactory::createOne();
        $conversation = ConversationFactory::new()->privateConversation()->create();
        ConversationParticipantFactory::new()
            ->admin()
            ->create(['conversation' => $conversation, 'user' => $user1]);

        $newUser = UserFactory::createOne();

        $this->authenticateUser($user1);

        $input = new AddParticipantsInput(
            userIds: [$newUser->getId()]
        );

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Cannot add participants to a private conversation');

        $this->processor->process(
            $input,
            null,
            ['id' => $conversation->getId()],
            []
        );
    }

    public function testAddParticipantWhoAlreadyExistsIsIgnored(): void
    {
        $admin = UserFactory::createOne();
        $existingMember = UserFactory::createOne();
        $conversation = ConversationFactory::new()->groupConversation('Test Group')->create();
        ConversationParticipantFactory::new()
            ->admin()
            ->create(['conversation' => $conversation, 'user' => $admin]);
        ConversationParticipantFactory::new()
            ->member()
            ->create(['conversation' => $conversation, 'user' => $existingMember]);

        $newUser = UserFactory::createOne();

        $this->authenticateUser($admin);

        $input = new AddParticipantsInput(
            userIds: [$existingMember->getId(), $newUser->getId()]
        );

        $result = $this->processor->process(
            $input,
            null,
            ['id' => $conversation->getId()],
            []
        );

        $this->assertCount(3, $result->getActiveParticipants());
    }

    public function testReactivateParticipantWhoLeftPreviously(): void
    {
        $admin = UserFactory::createOne();
        $leftUser = UserFactory::createOne();
        $conversation = ConversationFactory::new()->groupConversation('Test Group')->create();
        ConversationParticipantFactory::new()
            ->admin()
            ->create(['conversation' => $conversation, 'user' => $admin]);
        ConversationParticipantFactory::new()
            ->member()
            ->inactive()
            ->create(['conversation' => $conversation, 'user' => $leftUser]);

        $this->authenticateUser($admin);

        $input = new AddParticipantsInput(
            userIds: [$leftUser->getId()]
        );

        $result = $this->processor->process(
            $input,
            null,
            ['id' => $conversation->getId()],
            []
        );

        $this->assertCount(2, $result->getActiveParticipants());

        $reactivatedParticipant = $this->findParticipantByUserId(
            $result->getParticipants()->toArray(),
            $leftUser->getId()
        );
        $this->assertNotNull($reactivatedParticipant);
        $this->assertTrue($reactivatedParticipant->isActive());
        $this->assertNull($reactivatedParticipant->getLeftAt());
        $this->assertSame(ConversationParticipant::ROLE_MEMBER, $reactivatedParticipant->getRole());
    }

    public function testAddParticipantsWithInvalidUserIdsSkipsThem(): void
    {
        $admin = UserFactory::createOne();
        $conversation = ConversationFactory::new()->groupConversation('Test Group')->create();
        ConversationParticipantFactory::new()
            ->admin()
            ->create(['conversation' => $conversation, 'user' => $admin]);

        $validUser = UserFactory::createOne();

        $this->authenticateUser($admin);

        $input = new AddParticipantsInput(
            userIds: [99999, $validUser->getId(), 88888]
        );

        $result = $this->processor->process(
            $input,
            null,
            ['id' => $conversation->getId()],
            []
        );

        $this->assertCount(2, $result->getActiveParticipants());
        $this->assertTrue($this->hasParticipantWithUser($result->getParticipants()->toArray(), $validUser->getId()));
    }

    public function testAddParticipantsWithNoValidUsersThrowsValidationException(): void
    {
        $admin = UserFactory::createOne();
        $conversation = ConversationFactory::new()->groupConversation('Test Group')->create();
        ConversationParticipantFactory::new()
            ->admin()
            ->create(['conversation' => $conversation, 'user' => $admin]);

        $this->authenticateUser($admin);

        $input = new AddParticipantsInput(
            userIds: [99999, 88888]
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Aucun participant valide n\'a été ajouté');

        $this->processor->process(
            $input,
            null,
            ['id' => $conversation->getId()],
            []
        );
    }

    public function testAddParticipantsWithInvalidConversationIdThrowsBadRequest(): void
    {
        $admin = UserFactory::createOne();
        $this->authenticateUser($admin);

        $input = new AddParticipantsInput(
            userIds: [1]
        );

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Conversation not found');

        $this->processor->process(
            $input,
            null,
            ['id' => 99999],
            []
        );
    }

    public function testAddParticipantsWithoutAuthenticationThrowsException(): void
    {
        $conversation = ConversationFactory::new()->groupConversation('Test Group')->create();

        $input = new AddParticipantsInput(
            userIds: [1]
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('User must be authenticated');

        $this->processor->process(
            $input,
            null,
            ['id' => $conversation->getId()],
            []
        );
    }

    /**
     * @param array<ConversationParticipant> $participants
     */
    private function hasParticipantWithUser(array $participants, int $userId): bool
    {
        foreach ($participants as $participant) {
            if ($participant->getUser()->getId() === $userId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<ConversationParticipant> $participants
     */
    private function findParticipantByUserId(array $participants, int $userId): ?ConversationParticipant
    {
        foreach ($participants as $participant) {
            if ($participant->getUser()->getId() === $userId) {
                return $participant;
            }
        }

        return null;
    }

}
