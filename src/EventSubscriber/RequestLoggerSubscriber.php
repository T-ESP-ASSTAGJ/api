<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestLoggerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $req = $event->getRequest();
        $res = $event->getResponse();

        $this->logger->info(
            'HTTP request',
            [
                'method' => $req->getMethod(),
                'path' => $req->getPathInfo(),
                'status' => $res->getStatusCode(),
            ],
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}
