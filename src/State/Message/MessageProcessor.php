<?php

declare(strict_types=1);

namespace App\State\Message;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Message\MessageCreateInput;
use App\Entity\Conversation;
use App\Entity\Enum\MessageTypeEnum;
use App\Entity\Message;
use App\Entity\User;
use App\Service\ImageService;
use App\Service\Message\MessageMercurePublisherService;
use App\Service\Track\TrackService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @implements ProcessorInterface<MessageCreateInput, Message>
 */
final readonly class MessageProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
        private TrackService $trackService,
        private MessageMercurePublisherService $mercurePublisher,
        private ImageService $imageService,
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
        $content = $data->content;

        $message = new Message();
        $message->setAuthor($user);
        $message->setConversation($conversation);
        $message->setType($data->type);

        if (MessageTypeEnum::Image === $data->type) {
            $content = $this->imageService->saveBase64ToStorage($data->content, 'messages');
        }

        $message->setContent($content);
        if (MessageTypeEnum::Music === $data->type && $data->track) {
            $track = $this->trackService->findOrCreate($data->track);
            $message->setTrack($track);
        }

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        $this->mercurePublisher->publish($message);

        return $message;
    }
}
