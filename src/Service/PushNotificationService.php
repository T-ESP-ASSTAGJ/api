<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\UserRepository;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Psr\Log\LoggerInterface;

class PushNotificationService
{
    public function __construct(
        private Messaging $messaging,
        private UserRepository $userRepository,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<string, string|int|float|bool> $data
     */
    public function sendToUser(int $userId, string $title, string $body, array $data = []): void
    {
        $token = $this->userRepository->findDeviceTokenByUser($userId);

        if (null === $token) {
            $this->logger->info('No device token found for user {userId}', ['userId' => $userId]);

            return;
        }

        $message = CloudMessage::new()
            ->withNotification(Notification::create($title, $body))
            ->withData($data)
            ->withApnsConfig(
                ApnsConfig::new()
                    ->withSound('default')
                    ->withBadge(1)
                    ->withApsField('mutable-content', 1),
            )
        ;

        try {
            $this->messaging->send(
                $message->toToken($token),
            );

            $this->logger->info('Push sent', ['userId' => $userId]);
        } catch (\Throwable $e) {
            $this->logger->error('Push failed', [
                'userId' => $userId,
                'token' => $token,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
