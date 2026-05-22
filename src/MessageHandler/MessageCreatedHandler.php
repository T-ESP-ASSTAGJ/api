<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Message;
use App\Message\MessageCreatedMessage;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use App\Service\PushNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class MessageCreatedHandler
{
    public function __construct(
        private PushNotificationService $push,
        private UserRepository $userRepository,
        private ConversationRepository $conversationRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(MessageCreatedMessage $message): void
    {
        $sender = $this->userRepository->find($message->senderId);
        $conversation = $this->conversationRepository->find($message->conversationId);
        /** @var Message|null $messageEntity */
        $messageEntity = $this->entityManager->getRepository(Message::class)->find($message->messageId);

        if (!$sender || !$conversation || !$messageEntity) {
            return;
        }

        $participants = $conversation->getActiveParticipants();

        foreach ($participants as $participant) {
            $user = $participant->getUser();
            if ($user->getId() === $sender->getId()) {
                continue;
            }

            $setting = $user->getParameters()?->getNotifNewMessage() ?? true;

            if (false === $setting) {
                continue;
            }

            if ($conversation->getIsGroup()) {
                $title = $conversation->getGroupName();
                $body = \sprintf('%s just sent a message.', $sender->getUsername());
            } else {
                $title = $sender->getUsername();
                $body = 'just sent a message';
            }

            $this->push->sendToUser(
                userId: $user->getId(),
                title: $title,
                body: $body,
                data: [
                    'conversationId' => $conversation->getId(),
                    'profilePicture' => $sender->getProfilePicture(),
                ],
            );
        }
    }
}
