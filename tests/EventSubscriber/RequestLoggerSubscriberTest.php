<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\EventSubscriber\RequestLoggerSubscriber;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestLoggerSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $events = RequestLoggerSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(KernelEvents::RESPONSE, $events);
        $this->assertSame('onKernelResponse', $events[KernelEvents::RESPONSE]);
    }

    public function testOnKernelResponseLogsCorrectData(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $request = Request::create('/api/test', 'POST');
        $response = new Response('', 201);
        $kernel = $this->createMock(HttpKernelInterface::class);

        $event = new ResponseEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response
        );

        $logger->expects($this->once())
            ->method('info')
            ->with(
                'HTTP request',
                [
                    'method' => 'POST',
                    'path' => '/api/test',
                    'status' => 201,
                ]
            );

        $subscriber = new RequestLoggerSubscriber($logger);
        $subscriber->onKernelResponse($event);
    }
}
