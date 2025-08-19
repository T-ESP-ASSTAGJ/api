<?php

declare(strict_types=1);

namespace App\EventListener;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 100)]
readonly class EntityManagerResetListener
{
    public function __construct(
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof UniqueConstraintViolationException && !$this->em->isOpen()) {
            $this->logger->warning('EntityManager was closed due to constraint violation, resetting...', [
                'exception' => $exception->getMessage(),
                'request_uri' => $event->getRequest()->getRequestUri(),
            ]);

            $this->em->clear();
        }
    }
}
