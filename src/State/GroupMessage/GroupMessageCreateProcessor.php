<?php

declare(strict_types=1);

namespace App\State\GroupMessage;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\GroupMessage\GroupMessageGetOutput;
use App\ApiResource\GroupMessageCreateInput;
use App\Entity\GroupMessage;
use App\Entity\User;
use App\Service\Message\MusicMetadataService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements ProcessorInterface<GroupMessageCreateInput, GroupMessageGetOutput>
 */
final readonly class GroupMessageCreateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
        private Security $security,
        private MusicMetadataService $musicMetadataService,
    ) {
    }

    /**
     * @param GroupMessageCreateInput $data
     * @param array<string, mixed>    $uriVariables
     * @param array<string, mixed>    $context
     *
     * @return GroupMessageGetOutput
     */
    public function process(mixed $data, ?Operation $operation = null, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof GroupMessageCreateInput) {
            return $data;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new \RuntimeException('User must be authenticated');
        }

        $groupMessage = new GroupMessage();
        $groupMessage->setGroupId($data->groupId);
        $groupMessage->setAuthor($user);
        $groupMessage->setType($data->type);
        $groupMessage->setContent($data->content);

        if (GroupMessage::TYPE_MUSIC === $data->type && $data->track) {
            $groupMessage->setTrack($data->track);

            $trackMetadata = $this->musicMetadataService->getTrackMetadata($data->track);
            $groupMessage->setTrackMetadata($trackMetadata);
        }

        $violations = $this->validator->validate($groupMessage);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $this->em->persist($groupMessage);
        $this->em->flush();

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
