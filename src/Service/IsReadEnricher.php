<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Message;

final readonly class IsReadEnricher
{
    /**
     * @param object|iterable<object>|null $data
     */
    public function enrich(mixed $data): void
    {
        if (is_iterable($data)) {
            foreach ($data as $entity) {
                if ($entity instanceof Message) {
                    $entity->setRead($entity->isRead());
                }
            }
        } elseif ($data instanceof Message) {
            $data->setRead($data->isRead());
        }
    }
}
