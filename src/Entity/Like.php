<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post as ApiPost;
use ApiPlatform\Metadata\Put;
use App\ApiResource\Post\PostCreateInput;
use App\ApiResource\Post\PostGetOutput;
use App\Entity\Enum\LikeableTypeEnum;
use App\Entity\Interface\TimeStampableInterface;
use App\Entity\Type\LikeableTypeEnumType;
use App\State\Post\PostCreateProcessor;

use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    shortName: 'Post',
    operations: [
        new Get(output: PostGetOutput::class),
        new GetCollection(output: PostGetOutput::class),
        new ApiPost(
            input: PostCreateInput::class,
            output: PostGetOutput::class,
            processor: PostCreateProcessor::class
        ),
        new Put(output: PostGetOutput::class),
        new Delete(output: false),
    ]
)]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'post')]
#[ORM\UniqueConstraint(name: 'like_unique', columns: ['user_id', 'entity_id', 'entity_class'])]
class Like implements TimeStampableInterface
{
    use Trait\TimeStampableTrait;

    public const SERIALIZATION_GROUP_READ = 'like:read';
    public const SERIALIZATION_GROUP_DETAIL = 'like:detail';
    public const SERIALIZATION_GROUP_WRITE = 'like:write';

    #[ORM\Column(name: 'user_id', type: 'integer')]
    private int $userId;

    #[ORM\Column(name: 'entity_id', type: 'integer')]
    private int $entityId;


    #[ORM\Id]
    #[ORM\Column(
        name: 'entity_class',
        type: LikeableTypeEnumType::NAME,
        length: 50,
        enumType: LikeableTypeEnum::class
    )]
    private string $entityClass;

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): static
    {
        $this->userId = $userId;

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

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public function setEntityClass(string $entityClass): static
    {
        $this->entityClass = $entityClass;

        return $this;
    }
}
