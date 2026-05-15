<?php

declare(strict_types=1);

namespace App\Tests\State\Conversation;

use ApiPlatform\Metadata\Post;
use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use App\Entity\User;
use App\State\Conversation\ConversationLeaveProcessor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ConversationLeaveProcessorTest extends TestCase
{
    /**
     * @var EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private EntityManagerInterface $em;

    /**
     * @var Security&\PHPUnit\Framework\MockObject\MockObject
     */
    private Security $security;

    private ConversationLeaveProcessor $processor;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->processor = new ConversationLeaveProcessor($this->em, $this->security);
    }

    public function testThrowsWhenDataIsNotConversation(): void
    {
        $this->expectException(BadRequestException::class);
        $this->processor->process(new \stdClass(), new Post()); // @phpstan-ignore argument.type
    }

    public function testThrowsWhenUnauthenticated(): void
    {
        $this->security->method('getUser')->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->processor->process(new Conversation(), new Post());
    }

    public function testThrowsWhenNotMember(): void
    {
        $user = new User();
        $this->security->method('getUser')->willReturn($user);

        $conversation = new Conversation();
        // no participants — filter returns empty

        $this->expectException(NotFoundHttpException::class);
        $this->processor->process($conversation, new Post());
    }

    public function testLeavesDmConversation(): void
    {
        $user = new User();
        $this->security->method('getUser')->willReturn($user);
        $this->em->expects($this->once())->method('flush');

        $participant = new ConversationParticipant();
        $participant->setUser($user);

        $conversation = new Conversation();
        $conversation->setIsGroup(false);
        $conversation->addParticipant($participant);

        $result = $this->processor->process($conversation, new Post());

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertNotNull($participant->getLeftAt());
    }

    public function testLeavesGroupAndDeletesWhenLastMember(): void
    {
        $user = new User();
        $this->security->method('getUser')->willReturn($user);
        $this->em->expects($this->exactly(2))->method('flush');
        $this->em->expects($this->once())->method('remove');

        $participant = new ConversationParticipant();
        $participant->setUser($user);

        $conversation = new Conversation();
        $conversation->setIsGroup(true);
        $conversation->addParticipant($participant);

        $result = $this->processor->process($conversation, new Post());

        $this->assertInstanceOf(JsonResponse::class, $result);
        $data = json_decode($result->getContent(), true);
        $this->assertTrue($data['conversation_deleted']);
    }

    public function testLeavesGroupAndPromotesNextAdmin(): void
    {
        $admin = new User();
        $member = new User();

        $refAdmin = new \ReflectionProperty(User::class, 'id');
        $refAdmin->setAccessible(true);
        $refAdmin->setValue($admin, 1);

        $this->security->method('getUser')->willReturn($admin);
        $this->em->expects($this->once())->method('flush');

        $adminParticipant = new ConversationParticipant();
        $adminParticipant->setUser($admin);
        $adminParticipant->setRole(ConversationParticipant::ROLE_ADMIN);

        $memberParticipant = new ConversationParticipant();
        $memberParticipant->setUser($member);

        $conversation = new Conversation();
        $conversation->setIsGroup(true);
        $conversation->addParticipant($adminParticipant);
        $conversation->addParticipant($memberParticipant);

        $result = $this->processor->process($conversation, new Post());

        $this->assertInstanceOf(JsonResponse::class, $result);
    }
}
