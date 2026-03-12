<?php

declare(strict_types=1);

namespace App\Tests\State\Conversation;

use App\Entity\Conversation;
use App\Entity\User;
use App\Factory\ConversationFactory;
use App\Factory\ConversationParticipantFactory;
use App\Factory\UserFactory;
use App\State\Conversation\ConversationLeaveProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zenstruck\Foundry\Test\ResetDatabase;
use App\Tests\Trait\AuthenticationTrait;

class ConversationLeaveProcessorTest extends KernelTestCase
{
    use ResetDatabase;
    use AuthenticationTrait;

    private ConversationLeaveProcessor $processor;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->processor = self::getContainer()->get(ConversationLeaveProcessor::class);
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testLeavePrivateConversation(): void
    {
        $user1 = UserFactory::createOne();
        $user2 = UserFactory::createOne();
        $conversation = ConversationFactory::new()->privateConversation()->create();

        ConversationParticipantFactory::new()->create(['conversation' => $conversation, 'user' => $user1]);
        ConversationParticipantFactory::new()->create(['conversation' => $conversation, 'user' => $user2]);

        $this->authenticateUser($user1);

        $response = $this->processor->process($conversation->object());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Vous avez quitté la conversation', $data['message']);
        $this->assertArrayHasKey('left_at', $data);
        $this->assertArrayNotHasKey('conversation_deleted', $data);

        $this->em->refresh($conversation->object());
        $this->assertCount(1, $conversation->getActiveParticipants());
    }

    public function testLeaveGroupConversation(): void
    {
        $admin = UserFactory::createOne();
        $member = UserFactory::createOne();
        $conversation = ConversationFactory::new()->groupConversation('Test Group')->create();

        ConversationParticipantFactory::new()->admin()->create(['conversation' => $conversation, 'user' => $admin]);
        ConversationParticipantFactory::new()->member()->create(['conversation' => $conversation, 'user' => $member]);

        $this->authenticateUser($member);

        $response = $this->processor->process($conversation->object());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Vous avez quitté le groupe', $data['message']);
        $this->assertArrayHasKey('left_at', $data);
        $this->assertArrayNotHasKey('conversation_deleted', $data);

        $this->em->refresh($conversation->object());
        $this->assertCount(1, $conversation->getActiveParticipants());
    }

    public function testLeaveGroupAsLastMemberDeletesConversation(): void
    {
        $user = UserFactory::createOne();
        $conversation = ConversationFactory::new()->groupConversation('Test Group')->create();

        ConversationParticipantFactory::new()->admin()->create(['conversation' => $conversation, 'user' => $user]);

        $conversationId = $conversation->getId();

        $this->authenticateUser($user);

        $response = $this->processor->process($conversation->object());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Le groupe a été supprimé car vous étiez le dernier membre', $data['message']);
        $this->assertTrue($data['conversation_deleted']);
        $this->assertArrayHasKey('left_at', $data);

        $deletedConversation = $this->em->getRepository(Conversation::class)->find($conversationId);
        $this->assertNull($deletedConversation);
    }

    public function testLeaveConversationWhenNotParticipantThrowsNotFound(): void
    {
        $user = UserFactory::createOne();
        $otherUser = UserFactory::createOne();
        $conversation = ConversationFactory::new()->privateConversation()->create();

        ConversationParticipantFactory::new()->create(['conversation' => $conversation, 'user' => $otherUser]);

        $this->authenticateUser($user);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Vous n\'êtes pas membre de cette conversation');

        $this->processor->process($conversation->object());
    }

    public function testLeaveConversationWhenAlreadyLeftThrowsNotFound(): void
    {
        $user = UserFactory::createOne();
        $conversation = ConversationFactory::new()->privateConversation()->create();

        ConversationParticipantFactory::new()
            ->inactive()
            ->create(['conversation' => $conversation, 'user' => $user]);

        $this->authenticateUser($user);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Vous n\'êtes pas membre de cette conversation');

        $this->processor->process($conversation->object());
    }

    public function testLeaveConversationWithoutAuthenticationThrowsException(): void
    {
        $conversation = ConversationFactory::new()->privateConversation()->create();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('User must be authenticated');

        $this->processor->process($conversation->_get());
    }

    public function testLeaveGroupMarksParticipantAsInactive(): void
    {
        $admin = UserFactory::createOne();
        $member = UserFactory::createOne();
        $conversation = ConversationFactory::new()->groupConversation('Test Group')->create();

        ConversationParticipantFactory::new()->admin()->create(['conversation' => $conversation, 'user' => $admin]);
        $memberParticipant = ConversationParticipantFactory::new()
            ->member()
            ->create(['conversation' => $conversation, 'user' => $member]);

        $this->authenticateUser($member);

        $response = $this->processor->process($conversation->object());

        $this->assertInstanceOf(JsonResponse::class, $response);

        $this->em->refresh($memberParticipant->object());
        $this->assertFalse($memberParticipant->isActive());
        $this->assertNotNull($memberParticipant->getLeftAt());
    }

}
