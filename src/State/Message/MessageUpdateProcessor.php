<?php

declare(strict_types=1);

namespace App\State\Message;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\Message\MessageUpdateInput;
use App\Entity\Message;
use App\Entity\User;
use App\Service\Message\MessageMercurePublisherService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements ProcessorInterface<MessageUpdateInput, Message>
 */
final readonly class MessageUpdateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private Security $security,
        private MessageMercurePublisherService $mercurePublisher,
    ) {
    }

    /**
     * @param MessageUpdateInput   $data
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Message
    {
        /** @var Message|null $message */
        $message = $context['previous_data'] ?? null;

        if (!$message instanceof Message) {
            throw new NotFoundHttpException('Message not found');
        }

        /** @var User|null $user */
        $user = $this->security->getUser();
        if (null === $user) {
            throw new UnauthorizedHttpException('Bearer', 'Authentication required');
        }

        if ($data instanceof MessageUpdateInput && null !== $data->content) {
            $message->setContent($data->content);
        }

        $violations = $this->validator->validate($message);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $this->entityManager->flush();

        $this->mercurePublisher->publish($message);

        return $message;
    }
}
