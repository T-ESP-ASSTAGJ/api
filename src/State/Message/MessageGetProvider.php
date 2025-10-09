<?php

declare(strict_types=1);

namespace App\State\Message;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\DTO\Message\MessageGetOutput;
use App\Entity\Message;
use App\Entity\User;
use App\Repository\MessageRepository;
use App\Service\Message\MusicMetadataService;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProviderInterface<MessageGetOutput>
 */
final readonly class MessageGetProvider implements ProviderInterface
{
    public function __construct(
        private MessageRepository $messageRepository,
        private Security $security,
        private MusicMetadataService $musicMetadataService,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): MessageGetOutput
    {
        $messageId = $uriVariables['id'] ?? null;
        if (!$messageId) {
            throw new \InvalidArgumentException('Message ID is required');
        }

        $message = $this->messageRepository->find($messageId);
        if (!$message instanceof Message) {
            throw new \RuntimeException('Message not found');
        }

        /** @var User|null $user */
        $user = $this->security->getUser();

        if ($message->isMusicMessage() && $message->getTrack()) {
            $trackMetadata = $this->musicMetadataService->getTrackMetadata(
                $message->getTrack(),
                $user
            );
            $message->setTrackMetadata($trackMetadata);
        }

        return new MessageGetOutput(
            id: $message->getId(),
            type: $message->getType(),
            content: $message->getContent(),
            trackMetadata: $message->getTrackMetadata(),
            author: [
                'id' => $message->getAuthor()->getId(),
                'username' => $message->getAuthor()->getUsername(),
            ],
            createdAt: $message->getCreatedAt()->format('c')
        );
    }
}