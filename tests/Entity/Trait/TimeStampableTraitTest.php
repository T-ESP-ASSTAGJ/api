<?php

declare(strict_types=1);

namespace App\Tests\Entity\Trait;

use App\Entity\Trait\TimeStampableTrait;
use PHPUnit\Framework\TestCase;

class TimeStampableTraitTest extends TestCase
{
    public function testOnPrePersistSetsBothTimestamps(): void
    {
        $entity = $this->createTraitInstance();

        $entity->onPrePersist();

        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getUpdatedAt());
        $this->assertSame($entity->getCreatedAt(), $entity->getUpdatedAt());
    }

    public function testOnPrePersistDoesNotOverwriteExistingCreatedAt(): void
    {
        $entity = $this->createTraitInstance();
        $pastDate = new \DateTimeImmutable('2000-01-01 10:00:00');

        $entity->setCreatedAt($pastDate);

        $entity->onPrePersist();

        $this->assertSame($pastDate, $entity->getCreatedAt());
    }

    public function testOnPreUpdateOnlyChangesUpdatedAt(): void
    {
        $entity = $this->createTraitInstance();

        $pastDate = new \DateTimeImmutable('2020-01-01 10:00:00');
        $entity->setCreatedAt($pastDate);

        $entity->onPreUpdate();

        $this->assertSame($pastDate, $entity->getCreatedAt());
        $this->assertNotSame($pastDate, $entity->getUpdatedAt());
        $this->assertGreaterThan($pastDate, $entity->getUpdatedAt());
    }

    public function testManualSetters(): void
    {
        $entity = $this->createTraitInstance();
        $customDate = new \DateTimeImmutable('2024-12-25 00:00:00');

        $entity->setCreatedAt($customDate);
        $this->assertSame($customDate, $entity->getCreatedAt());
        $this->assertSame($customDate, $entity->getUpdatedAt());

        $newUpdate = new \DateTimeImmutable('2025-01-01 00:00:00');
        $entity->setUpdatedAt($newUpdate);
        $this->assertSame($customDate, $entity->getCreatedAt());
        $this->assertSame($newUpdate, $entity->getUpdatedAt());
    }

    private function createTraitInstance(): object
    {
        return new class {
            use TimeStampableTrait;
        };
    }
}
