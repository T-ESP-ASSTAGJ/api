<?php

declare(strict_types=1);

namespace App\Tests\State\Conversation;

use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\Conversation\ConversationCreateInput;
use App\Entity\Conversation;
use App\Entity\User;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use App\State\Conversation\ConversationCreateProcessor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConversationCreateProcessorTest extends TestCase
{
    /**
     * @var ValidatorInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private ValidatorInterface $validator;

    /**
     * @var Security&\PHPUnit\Framework\MockObject\MockObject
     */
    private Security $security;

    /**
     * @var UserRepository&\PHPUnit\Framework\MockObject\MockObject
     */
    private UserRepository $userRepo;

    /**
     * @var ConversationRepository&\PHPUnit\Framework\MockObject\MockObject
     */
    private ConversationRepository $conversationRepo;

    /**
     * @var EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private EntityManagerInterface $em;

    private ConversationCreateProcessor $processor;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->validator->method('validate')->willReturn(new ConstraintViolationList());
        $this->security = $this->createMock(Security::class);
        $this->userRepo = $this->createMock(UserRepository::class);
        $this->conversationRepo = $this->createMock(ConversationRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->processor = new ConversationCreateProcessor(
            $this->validator,
            $this->security,
            $this->userRepo,
            $this->conversationRepo,
            $this->em,
        );
    }

    public function testThrowsWhenPrivateConversationHasMultipleParticipants(): void
    {
        $currentUser = $this->makeUser(1);
        $this->security->method('getUser')->willReturn($currentUser);

        $input = new ConversationCreateInput(isGroup: false, participants: [2, 3]);

        $this->expectException(BadRequestException::class);
        $this->processor->process($input);
    }

    public function testThrowsWhenTargetUserNotFound(): void
    {
        $currentUser = $this->makeUser(1);
        $this->security->method('getUser')->willReturn($currentUser);
        $this->userRepo->method('find')->willReturn(null);

        $input = new ConversationCreateInput(isGroup: false, participants: [999]);

        $this->expectException(BadRequestException::class);
        $this->processor->process($input);
    }

    public function testThrowsWhenPrivateConversationAlreadyExists(): void
    {
        $currentUser = $this->makeUser(1);
        $targetUser = $this->makeUser(2);
        $this->security->method('getUser')->willReturn($currentUser);
        $this->userRepo->method('find')->willReturn($targetUser);
        $this->conversationRepo->method('findPrivateConversation')->willReturn(new Conversation());

        $input = new ConversationCreateInput(isGroup: false, participants: [2]);

        $this->expectException(BadRequestException::class);
        $this->processor->process($input);
    }

    public function testCreatesPrivateConversation(): void
    {
        $currentUser = $this->makeUser(1);
        $targetUser = $this->makeUser(2);
        $this->security->method('getUser')->willReturn($currentUser);
        $this->userRepo->method('find')->willReturn($targetUser);
        $this->conversationRepo->method('findPrivateConversation')->willReturn(null);
        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $input = new ConversationCreateInput(isGroup: false, participants: [2]);

        $result = $this->processor->process($input);

        $this->assertInstanceOf(Conversation::class, $result);
        $this->assertFalse($result->getIsGroup());
    }

    public function testThrowsWhenGroupHasNoName(): void
    {
        $currentUser = $this->makeUser(1);
        $targetUser = $this->makeUser(2);
        $this->security->method('getUser')->willReturn($currentUser);
        $this->userRepo->method('find')->willReturn($targetUser);

        $input = new ConversationCreateInput(isGroup: true, groupName: null, participants: [2]);

        $this->expectException(BadRequestException::class);
        $this->processor->process($input);
    }

    public function testCreatesGroupConversation(): void
    {
        $currentUser = $this->makeUser(1);
        $targetUser = $this->makeUser(2);
        $this->security->method('getUser')->willReturn($currentUser);
        $this->userRepo->method('find')->willReturn($targetUser);
        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $input = new ConversationCreateInput(isGroup: true, groupName: 'My Group', participants: [2]);

        $result = $this->processor->process($input);

        $this->assertInstanceOf(Conversation::class, $result);
        $this->assertTrue($result->getIsGroup());
        $this->assertSame('My Group', $result->getGroupName());
    }

    public function testSkipsSelfInParticipants(): void
    {
        $currentUser = $this->makeUser(1);
        $targetUser = $this->makeUser(2);
        $this->security->method('getUser')->willReturn($currentUser);
        $this->userRepo->method('find')->willReturnMap([
            [1, $currentUser],
            [2, $targetUser],
        ]);
        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        // participants includes currentUser (self) which should be skipped, and targetUser
        $input = new ConversationCreateInput(isGroup: true, groupName: 'Test', participants: [1, 2]);

        $result = $this->processor->process($input);

        // currentUser (as admin) + targetUser = 2 participants
        $this->assertCount(2, $result->getParticipants());
    }

    public function testSkipsDuplicateParticipant(): void
    {
        $currentUser = $this->makeUser(1);
        $targetUser = $this->makeUser(2);
        $this->security->method('getUser')->willReturn($currentUser);
        $this->userRepo->method('find')->willReturn($targetUser);
        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        // participants: same user twice → should be added only once
        $input = new ConversationCreateInput(isGroup: true, groupName: 'Test', participants: [2, 2]);

        $result = $this->processor->process($input);

        // currentUser (admin) + targetUser once = 2 participants
        $this->assertCount(2, $result->getParticipants());
    }

    public function testThrowsOnValidationFailure(): void
    {
        $currentUser = $this->makeUser(1);
        $targetUser = $this->makeUser(2);
        $this->security->method('getUser')->willReturn($currentUser);
        $this->userRepo->method('find')->willReturn($targetUser);
        $this->conversationRepo->method('findPrivateConversation')->willReturn(null);

        $violations = new ConstraintViolationList([$this->createMock(ConstraintViolationInterface::class)]);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->validator->method('validate')->willReturn($violations);
        $this->processor = new ConversationCreateProcessor(
            $this->validator,
            $this->security,
            $this->userRepo,
            $this->conversationRepo,
            $this->em,
        );

        $input = new ConversationCreateInput(isGroup: false, participants: [2]);

        $this->expectException(ValidationException::class);
        $this->processor->process($input);
    }

    public function testSkipsNonExistentUserInParticipantsList(): void
    {
        $currentUser = $this->makeUser(1);
        $targetUser = $this->makeUser(2);
        $this->security->method('getUser')->willReturn($currentUser);
        $this->userRepo->method('find')->willReturnMap([
            [2, $targetUser],
            [999, null],
        ]);
        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        // participants: [2, 999] where 999 doesn't exist → skipped inside loop
        $input = new ConversationCreateInput(isGroup: true, groupName: 'Test', participants: [2, 999]);

        $result = $this->processor->process($input);

        // currentUser (admin) + targetUser = 2 participants (999 skipped)
        $this->assertCount(2, $result->getParticipants());
    }

    private function makeUser(int $id): User
    {
        $user = new User();
        $ref = new \ReflectionProperty(User::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($user, $id);

        return $user;
    }
}
