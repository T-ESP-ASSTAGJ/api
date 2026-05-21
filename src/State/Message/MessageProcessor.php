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
use App\Message\MessageCreatedMessage;
use App\Service\ImageService;
use App\Service\Message\MessageMercurePublisherService;
use App\Service\Track\TrackService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Crée un nouveau message dans une conversation et déclenche la livraison en temps réel et par notification push.
 *
 * Pour les messages de type image, le contenu est téléversé vers Azure avant d'être stocké.
 * Pour les messages de type musique, la piste est résolue via TrackService.
 * Après la persistance, le message est publié sur Mercure et un MessageCreatedMessage
 * est envoyé sur le bus pour le traitement asynchrone des notifications push.
 *
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
        private MessageBusInterface $bus,
    ) {
    }

    /**
     * @param MessageCreateInput $data
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
            ->findOneBy(['id' => $data->conversationId])
        ;

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
        $this->bus->dispatch(
            new MessageCreatedMessage(
                $conversation->getId(),
                $user->getId(),
                $message->getId(),
            ),
        );

        return $message;
    }
}
