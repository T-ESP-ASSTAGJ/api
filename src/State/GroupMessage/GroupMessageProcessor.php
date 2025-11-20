<?php

declare(strict_types=1);

namespace App\State\GroupMessage;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Entity\GroupMessage;
use App\Entity\User;
use App\Service\Message\MusicMetadataService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements ProcessorInterface<GroupMessage, GroupMessage>
 */
final readonly class GroupMessageProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $persistProcessor,
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
        private Security $security,
        private MusicMetadataService $musicMetadataService,
    ) {
    }

    /**
     * @param GroupMessage         $data
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return GroupMessage
     */
    public function process(mixed $data, ?Operation $operation = null, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof GroupMessage) {
            return $data;
        }

        // Set author for new messages
        if (null === $data->getId()) {
            $user = $this->security->getUser();
            if (!$user instanceof User) {
                throw new \RuntimeException('User must be authenticated');
            }
            $data->setAuthor($user);
        }

        // Handle music metadata if type is music and track is provided
        if (GroupMessage::TYPE_MUSIC === $data->getType() && $data->getTrack()) {
            $trackMetadata = $this->musicMetadataService->getTrackMetadata($data->getTrack());
            $data->setTrackMetadata($trackMetadata);
        }

        $violations = $this->validator->validate($data);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
