<?php

declare(strict_types=1);

namespace App\State\Message;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\Message\MessageCreateInput;
use App\ApiResource\Message\MessageGetOutput;
use App\Entity\Message;
use App\Entity\User;
use App\Service\Message\MusicMetadataService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements ProcessorInterface<MessageCreateInput, MessageGetOutput>
 */
final readonly class MessageCreateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
        private Security $security,
        private MusicMetadataService $musicMetadataService,
    ) {
    }

    /**
     * @param MessageCreateInput   $data
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return MessageGetOutput
     */
    public function process(mixed $data, ?Operation $operation = null, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof MessageCreateInput) {
            return $data;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new \RuntimeException('User must be authenticated');
        }

        $message = new Message();
        $message->setConversationId($data->conversationId);
        $message->setAuthor($user);
        $message->setType($data->type);
        $message->setContent($data->content);

        if (Message::TYPE_MUSIC === $data->type && $data->track) {
            $message->setTrack($data->track);

            $trackMetadata = $this->musicMetadataService->getTrackMetadata($data->track);
            $message->setTrackMetadata($trackMetadata);
        }

        $violations = $this->validator->validate($message);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $this->em->persist($message);
        $this->em->flush();

        return new MessageGetOutput(
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
