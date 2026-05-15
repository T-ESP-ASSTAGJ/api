<?php

declare(strict_types=1);

namespace App\Tests\State\Conversation;

use ApiPlatform\Metadata\Post;
use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\Conversation\AddParticipantsInput;
use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use App\Entity\User;
use App\Repository\UserRepository;
use App\State\Conversation\AddParticipantsProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AddParticipantsProcessorTest extends TestCase
{
    /**
     * @var EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private EntityManagerInterface $em;

    /**
     * @var Security&\PHPUnit\Framework\MockObject\MockObject
     */
    private Security $security;

    /**
     * @var UserRepository&\PHPUnit\Framework\MockObject\MockObject
     */
    private UserRepository $userRepo;

    private AddParticipantsProcessor $processor;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->userRepo = $this->createMock(UserRepository::class);
        $this->processor = new AddParticipantsProcessor($this->em, $this->security, $this->userRepo);
    }

    public function testThrowsOnInvalidInput(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->processor->process(new \stdClass(), new Post(), ['id' => 1]); // @phpstan-ignore argument.type
    }

    public function testThrowsWhenUnauthenticated(): void
    {
        $this->security->method('getUser')->willReturn(null);
        $this->expectException(\RuntimeException::class);
        $this->processor->process(new AddParticipantsInput([1]), new Post(), ['id' => 1]);
    }

    public function testThrowsWhenNoConversationId(): void
    {
        $user = $this->makeUser(1);
        $this->security->method('getUser')->willReturn($user);
        $this->expectException(BadRequestHttpException::class);
        $this->processor->process(new AddParticipantsInput([1]), new Post(), []);
    }

    public function testThrowsWhenConversationNotFound(): void
    {
        $user = $this->makeUser(1);
        $this->security->method('getUser')->willReturn($user);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn(null);
        $this->em->method('getRepository')->willReturn($repo);

        $this->expectException(BadRequestHttpException::class);
        $this->processor->process(new AddParticipantsInput([1]), new Post(), ['id' => 1]);
    }

    public function testThrowsWhenPrivateConversation(): void
    {
        $admin = $this->makeUser(1);
        $this->security->method('getUser')->willReturn($admin);

        $conversation = new Conversation();
        $conversation->setIsGroup(false);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn($conversation);
        $this->em->method('getRepository')->willReturn($repo);

        $this->expectException(BadRequestHttpException::class);
        $this->processor->process(new AddParticipantsInput([2]), new Post(), ['id' => 1]);
    }

    public function testThrowsWhenNotAdmin(): void
    {
        $admin = $this->makeUser(1);
        $member = $this->makeUser(2);
        $this->security->method('getUser')->willReturn($member);

        $conversation = $this->makeConversation(true, $admin);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn($conversation);
        $this->em->method('getRepository')->willReturn($repo);

        $this->expectException(AccessDeniedHttpException::class);
        $this->processor->process(new AddParticipantsInput([3]), new Post(), ['id' => 1]);
    }

    public function testThrowsWhenNoValidUsersAdded(): void
    {
        $admin = $this->makeUser(1);
        $this->security->method('getUser')->willReturn($admin);

        $conversation = $this->makeConversation(true, $admin);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn($conversation);
        $this->em->method('getRepository')->willReturn($repo);
        $this->userRepo->method('find')->willReturn(null);

        $this->expectException(ValidationException::class);
        $this->processor->process(new AddParticipantsInput([999]), new Post(), ['id' => 1]);
    }

    public function testAddsNewParticipant(): void
    {
        $admin = $this->makeUser(1);
        $newUser = $this->makeUser(3);
        $this->security->method('getUser')->willReturn($admin);

        $conversation = $this->makeConversation(true, $admin);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn($conversation);
        $this->em->method('getRepository')->willReturn($repo);
        $this->userRepo->method('find')->willReturn($newUser);
        $this->em->expects($this->once())->method('flush');

        $result = $this->processor->process(new AddParticipantsInput([3]), new Post(), ['id' => 1]);

        $this->assertInstanceOf(Conversation::class, $result);
        $this->assertCount(2, $result->getActiveParticipants());
    }

    public function testReaddsFormerParticipant(): void
    {
        $admin = $this->makeUser(1);
        $former = $this->makeUser(3);
        $this->security->method('getUser')->willReturn($admin);

        $conversation = $this->makeConversation(true, $admin);

        $formerParticipant = new ConversationParticipant();
        $formerParticipant->setUser($former);
        $formerParticipant->leave();
        $conversation->addParticipant($formerParticipant);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn($conversation);
        $this->em->method('getRepository')->willReturn($repo);
        $this->userRepo->method('find')->willReturn($former);
        $this->em->expects($this->once())->method('flush');

        $result = $this->processor->process(new AddParticipantsInput([3]), new Post(), ['id' => 1]);

        $this->assertInstanceOf(Conversation::class, $result);
    }

    public function testSkipsAlreadyActiveParticipant(): void
    {
        $admin = $this->makeUser(1);
        $existing = $this->makeUser(2);
        $this->security->method('getUser')->willReturn($admin);

        $conversation = $this->makeConversation(true, $admin);

        // Add existing as active participant
        $existingParticipant = new ConversationParticipant();
        $existingParticipant->setUser($existing);
        $conversation->addParticipant($existingParticipant);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn($conversation);
        $this->em->method('getRepository')->willReturn($repo);
        $this->userRepo->method('find')->willReturn($existing);

        $this->expectException(ValidationException::class);
        $this->processor->process(new AddParticipantsInput([2]), new Post(), ['id' => 1]);
    }

    private function makeUser(int $id): User
    {
        $user = new User();
        $ref = new \ReflectionProperty(User::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($user, $id);

        return $user;
    }

    private function makeConversation(bool $isGroup, User $admin): Conversation
    {
        $conversation = new Conversation();
        $conversation->setIsGroup($isGroup);
        $ref = new \ReflectionProperty(Conversation::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($conversation, 1);

        $participant = new ConversationParticipant();
        $participant->setUser($admin);
        $participant->setRole(ConversationParticipant::ROLE_ADMIN);
        $conversation->addParticipant($participant);

        return $conversation;
    }
}
