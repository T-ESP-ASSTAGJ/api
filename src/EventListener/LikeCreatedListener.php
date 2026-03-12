<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Like;
use App\Message\LikeCreatedMessage;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsEntityListener(event: Events::postPersist, entity: Like::class)]
readonly class LikeCreatedListener
{
    public function __construct(
        private MessageBusInterface $bus,
    ) {}

    public function postPersist(Like $like): void
    {
        $this->bus->dispatch(new LikeCreatedMessage($like->getId()));
    }
}
