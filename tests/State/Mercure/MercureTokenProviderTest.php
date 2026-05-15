<?php

declare(strict_types=1);

namespace App\Tests\State\Mercure;

use ApiPlatform\Metadata\Get;
use App\ApiResource\Mercure\MercureTokenOutput;
use App\Entity\Conversation;
use App\Entity\User;
use App\Repository\ConversationRepository;
use App\State\Mercure\MercureTokenProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class MercureTokenProviderTest extends TestCase
{
    /**
     * @var Security&\PHPUnit\Framework\MockObject\MockObject
     */
    private Security $security;

    /**
     * @var ConversationRepository&\PHPUnit\Framework\MockObject\MockObject
     */
    private ConversationRepository $conversationRepository;

    private MercureTokenProvider $provider;

    protected function setUp(): void
    {
        $this->security = $this->createMock(Security::class);
        $this->conversationRepository = $this->createMock(ConversationRepository::class);
        $this->provider = new MercureTokenProvider(
            $this->security,
            $this->conversationRepository,
            'test-secret-key',
        );
    }

    public function testThrowsWhenUnauthenticated(): void
    {
        $this->security->method('getUser')->willReturn(null);

        $this->expectException(UnauthorizedHttpException::class);
        $this->provider->provide(new Get());
    }

    public function testReturnsTokenOutputWithTopics(): void
    {
        $user = new User();
        $this->security->method('getUser')->willReturn($user);

        $conversation = $this->createMock(Conversation::class);
        $conversation->method('getId')->willReturn(7);
        $this->conversationRepository->method('findByUser')->willReturn([$conversation]);

        $result = $this->provider->provide(new Get());

        $this->assertInstanceOf(MercureTokenOutput::class, $result);
        $this->assertContains('/conversations/7', $result->topics);
        $this->assertNotEmpty($result->token);

        // JWT format: 3 base64url parts
        $parts = explode('.', $result->token);
        $this->assertCount(3, $parts);
    }

    public function testReturnsEmptyTopicsWhenNoConversations(): void
    {
        $user = new User();
        $this->security->method('getUser')->willReturn($user);
        $this->conversationRepository->method('findByUser')->willReturn([]);

        $result = $this->provider->provide(new Get());

        $this->assertSame([], $result->topics);
    }
}
