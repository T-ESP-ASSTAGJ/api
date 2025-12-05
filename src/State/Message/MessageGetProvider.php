<?php

declare(strict_types=1);

namespace App\State\Message;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Message;
use App\Entity\User;
use App\Service\Message\MusicMetadataService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ProviderInterface<Message>
 */
final readonly class MessageGetProvider implements ProviderInterface
{
    public function __construct(
        /** @var ProviderInterface<Message> */
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
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
        $message = $this->itemProvider->provide($operation, $uriVariables, $context);

        if (!$message instanceof Message) {
            return $message;
        }

        /** @var User|null $user */
        $user = $this->security->getUser();

        // Refresh track metadata based on current user
        if ($message->isMusicMessage() && $message->getTrack()) {
            $trackMetadata = $this->musicMetadataService->getTrackMetadata(
                $message->getTrack(),
                $user
            );
            $message->setTrackMetadata($trackMetadata);
        }

        return $message;
    }
}
