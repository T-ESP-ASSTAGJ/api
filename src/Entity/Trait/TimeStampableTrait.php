<?php

declare(strict_types=1);

namespace App\Entity\Trait;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

trait TimeStampableTrait
{
    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    #[Groups([
        Message::SERIALIZATION_GROUP_READ,
        Message::SERIALIZATION_GROUP_DETAIL,
        Conversation::SERIALIZATION_GROUP_READ,
        Conversation::SERIALIZATION_GROUP_DETAIL,
        User::SERIALIZATION_GROUP_READ,
        User::SERIALIZATION_GROUP_DETAIL,
    ])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE)]
    #[Gedmo\Timestampable(on: 'update')]
    #[Groups([
        Message::SERIALIZATION_GROUP_DETAIL,
        Conversation::SERIALIZATION_GROUP_DETAIL,
        User::SERIALIZATION_GROUP_DETAIL,
    ])]
    private \DateTimeImmutable $updatedAt;

    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
