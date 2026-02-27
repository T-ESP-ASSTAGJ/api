<?php

declare(strict_types=1);

namespace App\State\Message;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\Message\MessageCreateInput;
use App\ApiResource\Message\MercureMessageOutput;
use App\Entity\Conversation;
use App\Entity\Enum\MercureTypeEnum;
use App\Entity\Message;
use App\Entity\User;
// use App\Service\Message\MusicMetadataService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements ProcessorInterface<MessageCreateInput, Message>
 */
final readonly class MessageProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private Security $security,
        private HubInterface $hub,
        //        private MusicMetadataService $musicMetadataService,
    ) {
    }

    /**
     * @param MessageCreateInput   $data
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return Message
     */
    public function process(mixed $data, ?Operation $operation = null, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof MessageCreateInput) {
            return $data;
        }

        /** @var User|null $user */
        $user = $this->security->getUser();
        if (null === $user) {
            throw new UnauthorizedHttpException('Bearer', 'Authentication required');
        }

        $conversation = $this->entityManager->getRepository(Conversation::class)
            ->findOneBy(['id' => $data->conversationId]);

        if (null === $conversation) {
            throw new NotFoundHttpException('Invalid conversation');
        }

        $message = new Message();
        $message->setAuthor($user);
        $message->setConversation($conversation);
        $message->setContent($data->content);
        $message->setType($data->type);

        //        if (Message::TYPE_MUSIC === $data->getType() && $data->getTrack()) {
        //            $trackMetadata = $this->musicMetadataService->getTrackMetadata($data->getTrack());
        //            $data->setTrackMetadata($trackMetadata);
        //        }

        $violations = $this->validator->validate($data);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        $mercureMessage = new MercureMessageOutput(MercureTypeEnum::Message, $message);
        $update = new Update(
            $mercureMessage->getTopic(),
            $mercureMessage->toJson()
        );
        $this->hub->publish($update);

        return $message;
    }
}

