<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\UserRepository;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Psr\Log\LoggerInterface;

/**
 * Envoie des notifications push Firebase Cloud Messaging (FCM) aux utilisateurs via leur jeton d'appareil stocké.
 *
 * Ignore silencieusement les utilisateurs sans jeton d'appareil enregistré et journalise les erreurs sans les propager,
 * de sorte qu'un échec de notification push ne brise jamais le flux appelant.
 */
class PushNotificationService
{
    public function __construct(
        private Messaging $messaging,
        private UserRepository $userRepository,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Envoie une notification push à l'appareil enregistré de l'utilisateur.
     *
     * Ne fait rien et journalise un message d'information lorsque l'utilisateur n'a pas de jeton d'appareil.
     * Les échecs FCM sont interceptés, journalisés comme erreurs et absorbés — les appelants ne sont pas affectés.
     *
     * @param array<string, string|int|float|bool> $data Paires clé/valeur supplémentaires transmises comme charge utile de données FCM
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
