<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\EventListener\EntityManagerResetListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class EntityManagerResetListenerTest extends TestCase
{
    /**
     * @var ManagerRegistry&MockObject
     */
    private ManagerRegistry $registry;

    private EntityManagerResetListener $listener;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->listener = new EntityManagerResetListener($this->registry);
    }

    public function testDoesNothingOnSubRequest(): void
    {
        $event = $this->makeEvent(HttpKernelInterface::SUB_REQUEST);

        $this->registry->expects($this->never())->method('getManagerNames');

        $this->listener->onKernelRequest($event);
    }

    public function testDoesNotResetOpenManager(): void
    {
        $event = $this->makeEvent(HttpKernelInterface::MAIN_REQUEST);

        $manager = $this->createMock(EntityManagerInterface::class);
        $manager->method('isOpen')->willReturn(true);

        $this->registry->method('getManagerNames')->willReturn(['default']);
        $this->registry->method('getManager')->with('default')->willReturn($manager);
        $this->registry->expects($this->never())->method('resetManager');

        $this->listener->onKernelRequest($event);
    }

    public function testResetsClosedManager(): void
    {
        $event = $this->makeEvent(HttpKernelInterface::MAIN_REQUEST);

        $manager = $this->createMock(EntityManagerInterface::class);
        $manager->method('isOpen')->willReturn(false);

        $this->registry->method('getManagerNames')->willReturn(['default']);
        $this->registry->method('getManager')->with('default')->willReturn($manager);
        $this->registry->expects($this->once())->method('resetManager')->with('default');

        $this->listener->onKernelRequest($event);
    }

    public function testResetsOnlyClosedManagersAmongMultiple(): void
    {
        $event = $this->makeEvent(HttpKernelInterface::MAIN_REQUEST);

        $openManager = $this->createMock(EntityManagerInterface::class);
        $openManager->method('isOpen')->willReturn(true);

        $closedManager = $this->createMock(EntityManagerInterface::class);
        $closedManager->method('isOpen')->willReturn(false);

        $this->registry->method('getManagerNames')->willReturn(['open', 'closed']);
        $this->registry->method('getManager')->willReturnMap([
            ['open', $openManager],
            ['closed', $closedManager],
        ]);
        $this->registry->expects($this->once())->method('resetManager')->with('closed');

        $this->listener->onKernelRequest($event);
    }

    public function testDoesNotResetNonEntityManager(): void
    {
        $event = $this->makeEvent(HttpKernelInterface::MAIN_REQUEST);

        $manager = $this->createMock(ObjectManager::class);

        $this->registry->method('getManagerNames')->willReturn(['default']);
        $this->registry->method('getManager')->with('default')->willReturn($manager);
        $this->registry->expects($this->never())->method('resetManager');

        $this->listener->onKernelRequest($event);
    }

    private function makeEvent(int $requestType): RequestEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);

        return new RequestEvent($kernel, Request::create('/'), $requestType);
    }
}
