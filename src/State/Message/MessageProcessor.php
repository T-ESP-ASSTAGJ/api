<?php

declare(strict_types=1);

namespace App\State\Message;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Entity\Message;
use App\Entity\User;
use App\Service\Message\MusicMetadataService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements ProcessorInterface<Message, Message>
 */
final readonly class MessageProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private ValidatorInterface $validator,
        private Security $security,
        private MusicMetadataService $musicMetadataService,
    ) {
    }

    /**
     * @param Message              $data
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return Message
     */
    public function process(mixed $data, ?Operation $operation = null, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof Message) {
            return $data;
        }

        if (null === $data->getId()) {
            $user = $this->security->getUser();
            if (!$user instanceof User) {
                throw new \RuntimeException('User must be authenticated');
            }
            $data->setAuthor($user);
        }

        if (Message::TYPE_MUSIC === $data->getType() && $data->getTrack()) {
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
