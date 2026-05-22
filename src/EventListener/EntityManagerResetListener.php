<?php

declare(strict_types=1);

namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final readonly class EntityManagerResetListener
{
    public function __construct(
        private ManagerRegistry $registry,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        foreach ($this->registry->getManagerNames() as $name) {
            $manager = $this->registry->getManager($name);
            if ($manager instanceof EntityManagerInterface && !$manager->isOpen()) {
                $this->registry->resetManager($name);
            }
        }
    }
}
