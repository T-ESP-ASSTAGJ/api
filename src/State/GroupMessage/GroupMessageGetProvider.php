<?php

declare(strict_types=1);

namespace App\State\GroupMessage;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\GroupMessage\GroupMessageGetOutput;
use App\Entity\GroupMessage;
use App\Entity\User;
use App\Service\Message\MusicMetadataService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProviderInterface<GroupMessageGetOutput>
 */
final readonly class GroupMessageGetProvider implements ProviderInterface
{
    public function __construct(
        private Security $security,
        private MusicMetadataService $musicMetadataService,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): GroupMessageGetOutput
    {
        $messageId = $uriVariables['id'] ?? null;
        if (!$messageId) {
            throw new \InvalidArgumentException('Group message ID is required');
        }

        /** @var GroupMessage|null $groupMessage */
        $groupMessage = $this->entityManager->getRepository(GroupMessage::class)->findOneBy(['id' => $messageId]);
        if (!$groupMessage) {
            throw new \RuntimeException('Group message not found');
        }

        /** @var User|null $user */
        $user = $this->security->getUser();

        if ($groupMessage->isMusicMessage() && $groupMessage->getTrack()) {
            $trackMetadata = $this->musicMetadataService->getTrackMetadata(
                $groupMessage->getTrack(),
                $user
            );
            $groupMessage->setTrackMetadata($trackMetadata);
        }

        return new GroupMessageGetOutput(
            id: $groupMessage->getId(),
            groupId: $groupMessage->getGroupId(),
            type: $groupMessage->getType(),
            content: $groupMessage->getContent(),
            trackMetadata: $groupMessage->getTrackMetadata(),
            author: [
                'id' => $groupMessage->getAuthor()->getId(),
                'username' => $groupMessage->getAuthor()->getUsername(),
            ],
            createdAt: $groupMessage->getCreatedAt()->format('c')
        );
    }
}
