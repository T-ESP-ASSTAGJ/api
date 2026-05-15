<?php

declare(strict_types=1);

namespace App\Tests\State\Message;

use ApiPlatform\Metadata\Post;
use App\ApiResource\Message\MessageCreateInput;
use App\ApiResource\Track\TrackInput;
use App\Entity\Conversation;
use App\Entity\Enum\MessageTypeEnum;
use App\Entity\Message;
use App\Entity\Track;
use App\Entity\User;
use App\Service\ImageService;
use App\Service\Message\MessageMercurePublisherService;
use App\Service\Track\TrackService;
use App\State\Message\MessageProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class MessageProcessorTest extends TestCase
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
     * @var TrackService&\PHPUnit\Framework\MockObject\MockObject
     */
    private TrackService $trackService;

    /**
     * @var MessageMercurePublisherService&\PHPUnit\Framework\MockObject\MockObject
     */
    private MessageMercurePublisherService $mercurePublisher;

    /**
     * @var ImageService&\PHPUnit\Framework\MockObject\MockObject
     */
    private ImageService $imageService;

    /**
     * @var MessageBusInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private MessageBusInterface $bus;

    private MessageProcessor $processor;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->trackService = $this->createMock(TrackService::class);
        $this->mercurePublisher = $this->createMock(MessageMercurePublisherService::class);
        $this->imageService = $this->createMock(ImageService::class);
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->processor = new MessageProcessor(
            $this->em,
            $this->security,
            $this->trackService,
            $this->mercurePublisher,
            $this->imageService,
            $this->bus,
        );
    }

    public function testReturnsDataWhenNotMessageCreateInput(): void
    {
        $data = new \stdClass();
        $result = $this->processor->process($data, new Post()); // @phpstan-ignore argument.type
        $this->assertSame($data, $result);
    }

    public function testThrowsWhenUnauthenticated(): void
    {
        $this->security->method('getUser')->willReturn(null);

        $input = new MessageCreateInput(conversationId: 1, type: MessageTypeEnum::Text, content: 'hello');
        $this->expectException(UnauthorizedHttpException::class);
        $this->processor->process($input, new Post());
    }

    public function testThrowsWhenConversationNotFound(): void
    {
        $user = new User();
        $this->security->method('getUser')->willReturn($user);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn(null);
        $this->em->method('getRepository')->willReturn($repo);

        $input = new MessageCreateInput(conversationId: 999, type: MessageTypeEnum::Text, content: 'hello');
        $this->expectException(NotFoundHttpException::class);
        $this->processor->process($input, new Post());
    }

    public function testCreatesTextMessage(): void
    {
        $user = $this->makeUser(42);
        $conv = $this->makeConversation();

        $this->security->method('getUser')->willReturn($user);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn($conv);
        $this->em->method('getRepository')->willReturn($repo);

        $this->em->expects($this->once())->method('persist')
            ->with($this->callback(function ($message) {
                $ref = new \ReflectionProperty(Message::class, 'id');
                $ref->setAccessible(true);
                $ref->setValue($message, 10);

                return true;
            }))
        ;
        $this->em->expects($this->once())->method('flush');
        $this->mercurePublisher->expects($this->once())->method('publish');
        $this->bus->expects($this->once())->method('dispatch')
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $input = new MessageCreateInput(conversationId: 1, type: MessageTypeEnum::Text, content: 'Hello World');

        $result = $this->processor->process($input, new Post());

        $this->assertInstanceOf(Message::class, $result);
        $this->assertSame('Hello World', $result->getContent());
    }

    public function testCreatesImageMessage(): void
    {
        $user = $this->makeUser(42);
        $conv = $this->makeConversation();

        $this->security->method('getUser')->willReturn($user);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn($conv);
        $this->em->method('getRepository')->willReturn($repo);

        $this->em->method('persist')
            ->with($this->callback(function ($message) {
                $ref = new \ReflectionProperty(Message::class, 'id');
                $ref->setAccessible(true);
                $ref->setValue($message, 10);

                return true;
            }))
        ;
        $this->imageService->method('saveBase64ToStorage')->willReturn('/uploads/messages/img.jpg');
        $this->bus->method('dispatch')->willReturn(new Envelope(new \stdClass()));

        $input = new MessageCreateInput(conversationId: 1, type: MessageTypeEnum::Image, content: 'data:image/png;base64,...');

        $result = $this->processor->process($input, new Post());

        $this->assertSame('/uploads/messages/img.jpg', $result->getContent());
    }

    public function testCreatesMusicMessage(): void
    {
        $user = $this->makeUser(42);
        $conv = $this->makeConversation();

        $this->security->method('getUser')->willReturn($user);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn($conv);
        $this->em->method('getRepository')->willReturn($repo);

        $this->em->method('persist')
            ->with($this->callback(function ($message) {
                $ref = new \ReflectionProperty(Message::class, 'id');
                $ref->setAccessible(true);
                $ref->setValue($message, 10);

                return true;
            }))
        ;
        $track = new Track();
        $this->trackService->method('findOrCreate')->willReturn($track);
        $this->bus->method('dispatch')->willReturn(new Envelope(new \stdClass()));

        $trackInput = new TrackInput('song-1', 'Song Name', 'Artist', 2020);
        $input = new MessageCreateInput(conversationId: 1, type: MessageTypeEnum::Music, content: null, track: $trackInput);

        $result = $this->processor->process($input, new Post());

        $this->assertSame($track, $result->getTrack());
    }

    private function makeUser(int $id): User
    {
        $user = new User();
        $ref = new \ReflectionProperty(User::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($user, $id);

        return $user;
    }

    private function makeConversation(): Conversation
    {
        $conv = new Conversation();
        $ref = new \ReflectionProperty(Conversation::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($conv, 1);

        return $conv;
    }
}
