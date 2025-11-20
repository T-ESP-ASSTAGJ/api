<?php

declare(strict_types=1);

namespace App\State\GroupMessage;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\GroupMessage;
use App\Entity\User;
use App\Service\Message\MusicMetadataService;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProviderInterface<GroupMessage>
 */
final readonly class GroupMessageGetProvider implements ProviderInterface
{
    public function __construct(
        private ProviderInterface $itemProvider,
        private Security $security,
        private MusicMetadataService $musicMetadataService,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $groupMessage = $this->itemProvider->provide($operation, $uriVariables, $context);

        if (!$groupMessage instanceof GroupMessage) {
            return $groupMessage;
        }

        /** @var User|null $user */
        $user = $this->security->getUser();

        // Refresh track metadata based on current user
        if ($groupMessage->isMusicMessage() && $groupMessage->getTrack()) {
            $trackMetadata = $this->musicMetadataService->getTrackMetadata(
                $groupMessage->getTrack(),
                $user
            );
            $groupMessage->setTrackMetadata($trackMetadata);
        }

        return $groupMessage;
    }
}
