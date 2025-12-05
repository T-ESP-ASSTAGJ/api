<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use App\ApiResource\Like\LikeCreateInput;
use App\Entity\Enum\LikeableTypeEnum;
use App\Entity\Interface\TimeStampableInterface;
use App\State\Like\LikeProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Like',
    operations: [
        new Post(
            uriTemplate: '/likes',
            status: 204,
            input: LikeCreateInput::class,
            output: false,
            processor: LikeProcessor::class
        ),
        new Delete(
            uriTemplate: '/likes/{id}',
            output: false,
            processor: LikeProcessor::class,
        ),
    ]
)]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: '`like`')]
#[ORM\UniqueConstraint(name: 'like_unique', columns: ['user_id', 'entity_id', 'entity_class'])]
class Like implements TimeStampableInterface
{
    use Trait\TimeStampableTrait;

    public const SERIALIZATION_GROUP_READ = 'like:read';
    public const SERIALIZATION_GROUP_DETAIL = 'like:detail';
    public const SERIALIZATION_GROUP_WRITE = 'like:write';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    #[ORM\Column(name: 'entity_id', type: 'integer', nullable: false)]
    private int $entityId;

    #[Assert\Choice(callback: [LikeableTypeEnum::class, 'values'])]
    #[ORM\Column(name: 'entity_class', type: 'string', length: 50, nullable: false, enumType: LikeableTypeEnum::class)]
    private LikeableTypeEnum $entityClass;

    public function getUserId(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }

    public function setEntityId(int $entityId): static
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function getEntityClass(): LikeableTypeEnum
    {
        return $this->entityClass;
    }

    public function setEntityClass(LikeableTypeEnum $entityClass): static
    {
        $this->entityClass = $entityClass;

        return $this;
    }
}
