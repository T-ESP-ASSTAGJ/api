<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Repository\UserRepository;
use App\Service\PushNotificationService;
use Kreait\Firebase\Contract\Messaging;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PushNotificationServiceTest extends TestCase
{
    private Messaging&\PHPUnit\Framework\MockObject\MockObject $messaging;

    private UserRepository&\PHPUnit\Framework\MockObject\MockObject $userRepository;

    private LoggerInterface&\PHPUnit\Framework\MockObject\MockObject $logger;

    private PushNotificationService $service;

    protected function setUp(): void
    {
        $this->messaging = $this->createMock(Messaging::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->service = new PushNotificationService($this->messaging, $this->userRepository, $this->logger);
    }

    public function testSendToUserLogsInfoAndReturnsWhenNoDeviceToken(): void
    {
        $this->userRepository->method('findDeviceTokenByUser')->with(1)->willReturn(null);

        $this->logger->expects($this->once())->method('info')
            ->with('No device token found for user {userId}', ['userId' => 1])
        ;
        $this->messaging->expects($this->never())->method('send');

        $this->service->sendToUser(1, 'Title', 'Body');
    }

    public function testSendToUserSendsMessageAndLogsSuccess(): void
    {
        $this->userRepository->method('findDeviceTokenByUser')->willReturn('device-token-xyz');

        $this->messaging->expects($this->once())->method('send');
        $this->logger->expects($this->once())->method('info')
            ->with('Push sent', ['userId' => 2])
        ;

        $this->service->sendToUser(2, 'Hello', 'World', ['key' => 'value']);
    }

    public function testSendToUserLogsErrorWhenSendThrows(): void
    {
        $this->userRepository->method('findDeviceTokenByUser')->willReturn('device-token');

        $this->messaging->method('send')->willThrowException(new \RuntimeException('Network error'));

        $this->logger->expects($this->once())->method('error')
            ->with('Push failed', $this->arrayHasKey('error'))
        ;

        $this->service->sendToUser(3, 'Title', 'Body');
    }
}
