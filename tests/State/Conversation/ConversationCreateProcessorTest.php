<?php

declare(strict_types=1);

namespace App\Tests\State\Conversation;

use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\Conversation\ConversationCreateInput;
use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use App\Entity\User;
use App\Factory\UserFactory;
use App\State\Conversation\ConversationCreateProcessor;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;
use App\Tests\Trait\AuthenticationTrait;

class ConversationCreateProcessorTest extends KernelTestCase
{
    use ResetDatabase;
    use AuthenticationTrait;

    private ConversationCreateProcessor $processor;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->processor = self::getContainer()->get(ConversationCreateProcessor::class);
    }

    public function testCreatePrivateConversation(): void
    {
        $currentUser = UserFactory::createOne();
        $otherUser = UserFactory::createOne();

        $this->authenticateUser($currentUser);

        $input = new ConversationCreateInput(
            isGroup: false,
            groupName: null,
            participants: [$otherUser->getId()]
        );

        $conversation = $this->processor->process($input);

        $this->assertInstanceOf(Conversation::class, $conversation);
        $this->assertFalse($conversation->getIsGroup());
        $this->assertNull($conversation->getGroupName());
        $this->assertCount(2, $conversation->getParticipants());

        $participants = $conversation->getParticipants()->toArray();
        $this->assertTrue($this->hasParticipantWithRole($participants, ConversationParticipant::ROLE_ADMIN));
        $this->assertTrue($this->hasParticipantWithUser($participants, $currentUser->getId()));
        $this->assertTrue($this->hasParticipantWithUser($participants, $otherUser->getId()));
    }

    public function testCreateGroupConversationWithValidData(): void
    {
        $currentUser = UserFactory::createOne();
        $user1 = UserFactory::createOne();
        $user2 = UserFactory::createOne();

        $this->authenticateUser($currentUser);

        $input = new ConversationCreateInput(
            isGroup: true,
            groupName: 'Test Group',
            participants: [$user1->getId(), $user2->getId()]
        );

        $conversation = $this->processor->process($input);

        $this->assertInstanceOf(Conversation::class, $conversation);
        $this->assertTrue($conversation->getIsGroup());
        $this->assertSame('Test Group', $conversation->getGroupName());
        $this->assertCount(3, $conversation->getParticipants());

        $participants = $conversation->getParticipants()->toArray();
        $creatorParticipant = $this->findParticipantByUserId($participants, $currentUser->getId());
        $this->assertNotNull($creatorParticipant);
        $this->assertSame(ConversationParticipant::ROLE_ADMIN, $creatorParticipant->getRole());

        $this->assertTrue($this->hasParticipantWithUser($participants, $user1->getId()));
        $this->assertTrue($this->hasParticipantWithUser($participants, $user2->getId()));
    }

    public function testCreateGroupConversationWithoutGroupNameThrowsValidationException(): void
    {
        $currentUser = UserFactory::createOne();
        $otherUser = UserFactory::createOne();

        $this->authenticateUser($currentUser);

        $input = new ConversationCreateInput(
            isGroup: true,
            groupName: null,
            participants: [$otherUser->getId()]
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Le nom du groupe est obligatoire');

        $this->processor->process($input);
    }

    public function testCreateGroupConversationWithoutParticipantsThrowsValidationException(): void
    {
        $currentUser = UserFactory::createOne();

        $this->authenticateUser($currentUser);

        $input = new ConversationCreateInput(
            isGroup: true,
            groupName: 'Test Group',
            participants: []
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Vous devez sélectionner au moins un participant');

        $this->processor->process($input);
    }

    public function testCreateConversationWithNullParticipantsForGroup(): void
    {
        $currentUser = UserFactory::createOne();

        $this->authenticateUser($currentUser);

        $input = new ConversationCreateInput(
            isGroup: true,
            groupName: 'Test Group',
            participants: null
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Vous devez sélectionner au moins un participant');

        $this->processor->process($input);
    }

    public function testCreateConversationIgnoresCurrentUserInParticipantsList(): void
    {
        $currentUser = UserFactory::createOne();
        $otherUser = UserFactory::createOne();

        $this->authenticateUser($currentUser);

        $input = new ConversationCreateInput(
            isGroup: false,
            groupName: null,
            participants: [$currentUser->getId(), $otherUser->getId()]
        );

        $conversation = $this->processor->process($input);

        $this->assertCount(2, $conversation->getParticipants());

        $participants = $conversation->getParticipants()->toArray();
        $currentUserParticipants = array_filter(
            $participants,
            fn (ConversationParticipant $p) => $p->getUser()->getId() === $currentUser->getId()
        );
        $this->assertCount(1, $currentUserParticipants);
    }

    public function testCreateConversationSkipsInvalidUserIds(): void
    {
        $currentUser = UserFactory::createOne();
        $validUser = UserFactory::createOne();

        $this->authenticateUser($currentUser);

        $input = new ConversationCreateInput(
            isGroup: false,
            groupName: null,
            participants: [99999, $validUser->getId(), 88888]
        );

        $conversation = $this->processor->process($input);

        $this->assertCount(2, $conversation->getParticipants());
        $this->assertTrue($this->hasParticipantWithUser($conversation->getParticipants()->toArray(), $validUser->getId()));
    }

    public function testCreateConversationWithoutAuthenticationThrowsException(): void
    {
        $input = new ConversationCreateInput(
            isGroup: false,
            groupName: null,
            participants: []
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('User must be authenticated');

        $this->processor->process($input);
    }

    /**
     * @param array<ConversationParticipant> $participants
     */
    private function hasParticipantWithRole(array $participants, string $role): bool
    {
        foreach ($participants as $participant) {
            if ($participant->getRole() === $role) {
                return true;
            }
        }

        return false;
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
