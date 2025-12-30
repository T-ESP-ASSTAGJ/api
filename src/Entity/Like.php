<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use App\ApiResource\Like\LikeCreateInput;
use App\Entity\Enum\LikeableTypeEnum;
use App\Entity\Interface\LikeableInterface;
use App\Entity\Interface\TimeStampableInterface;
use App\State\Like\LikeProcessor;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
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
            security: 'object.getUser() == user',
            output: false,
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

    #[Groups([self::SERIALIZATION_GROUP_READ])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[Groups([self::SERIALIZATION_GROUP_READ])]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    #[ORM\Column(name: 'entity_id', type: 'integer', nullable: false)]
    private int $entityId;

    #[Groups([self::SERIALIZATION_GROUP_READ])]
    #[Assert\Choice(callback: [LikeableTypeEnum::class, 'values'])]
    #[ORM\Column(name: 'entity_class', type: 'string', length: 255, nullable: false, enumType: LikeableTypeEnum::class)]
    private LikeableTypeEnum $entityClass;

    #[Groups([self::SERIALIZATION_GROUP_READ])]
    private ?object $likedEntity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
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

    public function getEntityClass(): string
    {
        return $this->entityClass->value;
    }

    public function setEntityClass(LikeableTypeEnum $entityClass): static
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    public function getLikedEntity(): ?object
    {
        return $this->likedEntity;
    }

    public function setLikedEntity(?object $likedEntity): void
    {
        $this->likedEntity = $likedEntity;
    }

    #[ORM\PostPersist]
    public function incrementLikesCount(PostPersistEventArgs $event): void
    {
        $this->updateLikesCount($event, 1);
    }

    #[ORM\PostRemove]
    public function decrementLikesCount(PostRemoveEventArgs $event): void
    {
        $this->updateLikesCount($event, -1);
    }

    private function updateLikesCount(PostPersistEventArgs|PostRemoveEventArgs $event, int $diff): void
    {
        $objectManager = $event->getObjectManager();
        $className = $this->entityClass->value;

        $objectManager->createQuery(sprintf('UPDATE %s e SET e.likesCount = e.likesCount + :diff WHERE e.id = :id', $className))
            ->setParameter('diff', $diff)
            ->setParameter('id', $this->entityId)
            ->execute();

        // If entity is currently loaded in memory, refresh it or update the value
        $entity = $objectManager->getUnitOfWork()->tryGetById($this->entityId, $className);
        if ($entity instanceof LikeableInterface) {
            $entity->setLikesCount($entity->getLikesCount() + $diff);
        }
    }
}
