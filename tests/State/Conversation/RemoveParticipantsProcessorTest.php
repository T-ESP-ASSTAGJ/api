<?php

declare(strict_types=1);

namespace App\Tests\State\Conversation;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\Conversation\RemoveParticipantsInput;
use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use App\Entity\User;
use App\State\Conversation\RemoveParticipantsProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RemoveParticipantsProcessorTest extends TestCase
{
    /**
     * @var EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private EntityManagerInterface $em;

    /**
     * @var Security&\PHPUnit\Framework\MockObject\MockObject
     */
    private Security $security;

    private RemoveParticipantsProcessor $processor;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->processor = new RemoveParticipantsProcessor($this->em, $this->security);
    }

    public function testThrowsOnInvalidInput(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->processor->process(new \stdClass(), new Delete(), ['id' => 1]); // @phpstan-ignore argument.type
    }

    public function testThrowsWhenNoConversationId(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->processor->process(new RemoveParticipantsInput([1]), new Delete(), []);
    }

    public function testThrowsWhenConversationNotFound(): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn(null);
        $this->em->method('getRepository')->willReturn($repo);

        $this->expectException(BadRequestHttpException::class);
        $this->processor->process(new RemoveParticipantsInput([1]), new Delete(), ['id' => 999]);
    }

    public function testThrowsWhenPrivateConversation(): void
    {
        $conversation = new Conversation();
        $conversation->setIsGroup(false);
        $this->mockConversationRepo($conversation);

        $this->expectException(BadRequestHttpException::class);
        $this->processor->process(new RemoveParticipantsInput([1]), new Delete(), ['id' => 1]);
    }

    public function testThrowsWhenUnauthenticated(): void
    {
        $admin = $this->makeUser(1);
        $conversation = $this->makeGroupConversation($admin);
        $this->mockConversationRepo($conversation);
        $this->security->method('getUser')->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->processor->process(new RemoveParticipantsInput([2]), new Delete(), ['id' => 1]);
    }

    public function testThrowsWhenNotAdmin(): void
    {
        $admin = $this->makeUser(1);
        $member = $this->makeUser(2);
        $conversation = $this->makeGroupConversation($admin);
        $this->mockConversationRepo($conversation);
        $this->security->method('getUser')->willReturn($member);

        $this->expectException(AccessDeniedHttpException::class);
        $this->processor->process(new RemoveParticipantsInput([3]), new Delete(), ['id' => 1]);
    }

    public function testThrowsWhenNobodyRemoved(): void
    {
        $admin = $this->makeUser(1);
        $conversation = $this->makeGroupConversation($admin);
        $this->mockConversationRepo($conversation);
        $this->security->method('getUser')->willReturn($admin);

        // trying to remove self — should be skipped
        $this->expectException(ValidationException::class);
        $this->processor->process(new RemoveParticipantsInput([1]), new Delete(), ['id' => 1]);
    }

    public function testRemovesParticipantSuccessfully(): void
    {
        $admin = $this->makeUser(1);
        $target = $this->makeUser(2);
        $conversation = $this->makeGroupConversation($admin);

        $targetParticipant = new ConversationParticipant();
        $targetParticipant->setUser($target);
        $conversation->addParticipant($targetParticipant);

        $this->mockConversationRepo($conversation);
        $this->security->method('getUser')->willReturn($admin);
        $this->em->expects($this->once())->method('flush');

        $result = $this->processor->process(new RemoveParticipantsInput([2]), new Delete(), ['id' => 1]);

        $this->assertInstanceOf(Conversation::class, $result);
        $this->assertNotNull($targetParticipant->getLeftAt());
    }

    public function testDeletesGroupWhenLastMembersRemoved(): void
    {
        $admin = $this->makeUser(1);
        $target = $this->makeUser(2);

        // Conversation where removing target leaves nobody active (admin already left)
        $conversation = new Conversation();
        $conversation->setIsGroup(true);
        $ref = new \ReflectionProperty(Conversation::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($conversation, 1);

        $adminParticipant = new ConversationParticipant();
        $adminParticipant->setUser($admin);
        $adminParticipant->setRole(ConversationParticipant::ROLE_ADMIN);
        $conversation->addParticipant($adminParticipant);

        $targetParticipant = new ConversationParticipant();
        $targetParticipant->setUser($target);
        $conversation->addParticipant($targetParticipant);

        $this->mockConversationRepo($conversation);
        $this->security->method('getUser')->willReturn($admin);

        // Make admin "leave" before remove is checked
        $adminParticipant->leave();

        $this->em->expects($this->exactly(2))->method('flush');
        $this->em->expects($this->once())->method('remove');

        $result = $this->processor->process(new RemoveParticipantsInput([2]), new Delete(), ['id' => 1]);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $data = json_decode($result->getContent(), true);
        $this->assertTrue($data['conversation_deleted']);
    }

    public function testSkipsSelfWhenIncludedInUserIds(): void
    {
        $admin = $this->makeUser(1);
        $target = $this->makeUser(2);
        $conversation = $this->makeGroupConversation($admin);

        $targetParticipant = new ConversationParticipant();
        $targetParticipant->setUser($target);
        $conversation->addParticipant($targetParticipant);

        $this->mockConversationRepo($conversation);
        $this->security->method('getUser')->willReturn($admin);
        $this->em->expects($this->once())->method('flush');

        // Include admin's own id (1) which should be skipped, only target (2) removed
        $result = $this->processor->process(new RemoveParticipantsInput([1, 2]), new Delete(), ['id' => 1]);

        $this->assertInstanceOf(Conversation::class, $result);
        $this->assertNotNull($targetParticipant->getLeftAt());
    }

    public function testSkipsNonExistentParticipantId(): void
    {
        $admin = $this->makeUser(1);
        $target = $this->makeUser(2);
        $conversation = $this->makeGroupConversation($admin);

        $targetParticipant = new ConversationParticipant();
        $targetParticipant->setUser($target);
        $conversation->addParticipant($targetParticipant);

        $this->mockConversationRepo($conversation);
        $this->security->method('getUser')->willReturn($admin);
        $this->em->expects($this->once())->method('flush');

        // User 999 doesn't exist in conversation, user 2 is removed
        $result = $this->processor->process(new RemoveParticipantsInput([999, 2]), new Delete(), ['id' => 1]);

        $this->assertInstanceOf(Conversation::class, $result);
        $this->assertNotNull($targetParticipant->getLeftAt());
    }

    private function makeUser(int $id): User
    {
        $user = new User();
        $ref = new \ReflectionProperty(User::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($user, $id);

        return $user;
    }

    private function makeGroupConversation(User $admin): Conversation
    {
        $conversation = new Conversation();
        $conversation->setIsGroup(true);
        $ref = new \ReflectionProperty(Conversation::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($conversation, 1);

        $adminParticipant = new ConversationParticipant();
        $adminParticipant->setUser($admin);
        $adminParticipant->setRole(ConversationParticipant::ROLE_ADMIN);
        $conversation->addParticipant($adminParticipant);

        return $conversation;
    }

    private function mockConversationRepo(Conversation $conversation): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn($conversation);
        $this->em->method('getRepository')->willReturn($repo);
    }
}
