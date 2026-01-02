<?php

declare(strict_types=1);

namespace App\Tests\Entity\Trait;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class TimeStampableTraitTest extends TestCase
{
    public function testSetCreatedAtSetsCreatedAtAndUpdatedAt(): void
    {
        $entity = new User();

        $entity->setCreatedAt();

        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getUpdatedAt());

        // Both should be set to the same time
        $this->assertEquals($entity->getCreatedAt(), $entity->getUpdatedAt());
    }

    public function testSetUpdatedAtUpdatesOnlyUpdatedAt(): void
    {
        $entity = new User();
        $entity->setCreatedAt();

        $originalCreatedAt = $entity->getCreatedAt();
        $originalUpdatedAt = $entity->getUpdatedAt();

        // Wait a tiny bit to ensure time difference
        usleep(1000);

        $entity->setUpdatedAt();

        // CreatedAt should remain unchanged
        $this->assertSame($originalCreatedAt, $entity->getCreatedAt());

        // UpdatedAt should be different from original
        $this->assertNotEquals($originalUpdatedAt, $entity->getUpdatedAt());
        $this->assertGreaterThan($originalUpdatedAt, $entity->getUpdatedAt());
    }

    public function testTimestampsAreImmutable(): void
    {
        $entity = new User();
        $entity->setCreatedAt();

        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getUpdatedAt());
    }
}
