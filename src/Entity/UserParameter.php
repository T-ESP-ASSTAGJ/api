<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use App\Entity\Interface\TimeStampableInterface;
use App\Repository\UserParameterRepository;
use App\State\User\UserParameterProcessor;
use App\State\User\UserParameterProvider;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    shortName: 'UserParameter',
    operations: [
        new Get(
            uriTemplate: '/users/me/parameters',
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_READ]],
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            read: true,
            provider: UserParameterProvider::class,
        ),
        new Patch(
            uriTemplate: '/users/me/parameters',
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_READ]],
            denormalizationContext: ['groups' => [self::SERIALIZATION_GROUP_WRITE]],
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            read: true,
            provider: UserParameterProvider::class,
            processor: UserParameterProcessor::class,
        ),
    ],
)]
#[ORM\Entity(repositoryClass: UserParameterRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'user_parameter')]
class UserParameter implements TimeStampableInterface
{
    use Trait\TimeStampableTrait;

    public const SERIALIZATION_GROUP_READ = 'user_parameter:read';
    public const SERIALIZATION_GROUP_WRITE = 'user_parameter:write';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'parameters')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[Groups([self::SERIALIZATION_GROUP_READ, self::SERIALIZATION_GROUP_WRITE, User::SERIALIZATION_GROUP_DETAIL])]
    private bool $isFollowersPublic = true;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[Groups([self::SERIALIZATION_GROUP_READ, self::SERIALIZATION_GROUP_WRITE, User::SERIALIZATION_GROUP_DETAIL])]
    private bool $isFollowingPublic = true;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[Groups([self::SERIALIZATION_GROUP_READ, self::SERIALIZATION_GROUP_WRITE, User::SERIALIZATION_GROUP_DETAIL])]
    private bool $isStatsPublic = true;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[Groups([self::SERIALIZATION_GROUP_READ, self::SERIALIZATION_GROUP_WRITE, User::SERIALIZATION_GROUP_DETAIL])]
    private bool $isPlaylistPublic = true;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[Groups([self::SERIALIZATION_GROUP_READ, self::SERIALIZATION_GROUP_WRITE, User::SERIALIZATION_GROUP_DETAIL])]
    private bool $isLikesPublic = true;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[Groups([self::SERIALIZATION_GROUP_READ, self::SERIALIZATION_GROUP_WRITE, User::SERIALIZATION_GROUP_DETAIL])]
    private bool $notifNewFollower = true;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[Groups([self::SERIALIZATION_GROUP_READ, self::SERIALIZATION_GROUP_WRITE, User::SERIALIZATION_GROUP_DETAIL])]
    private bool $notifNewLike = true;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[Groups([self::SERIALIZATION_GROUP_READ, self::SERIALIZATION_GROUP_WRITE, User::SERIALIZATION_GROUP_DETAIL])]
    private bool $notifNewComment = true;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[Groups([self::SERIALIZATION_GROUP_READ, self::SERIALIZATION_GROUP_WRITE, User::SERIALIZATION_GROUP_DETAIL])]
    private bool $notifNewMessage = true;

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

    public function getIsFollowersPublic(): bool
    {
        return $this->isFollowersPublic;
    }

    public function setIsFollowersPublic(bool $isFollowersPublic): static
    {
        $this->isFollowersPublic = $isFollowersPublic;

        return $this;
    }

    public function getIsFollowingPublic(): bool
    {
        return $this->isFollowingPublic;
    }

    public function setIsFollowingPublic(bool $isFollowingPublic): static
    {
        $this->isFollowingPublic = $isFollowingPublic;

        return $this;
    }

    public function getIsStatsPublic(): bool
    {
        return $this->isStatsPublic;
    }

    public function setIsStatsPublic(bool $isStatsPublic): static
    {
        $this->isStatsPublic = $isStatsPublic;

        return $this;
    }

    public function getIsPlaylistPublic(): bool
    {
        return $this->isPlaylistPublic;
    }

    public function setIsPlaylistPublic(bool $isPlaylistPublic): static
    {
        $this->isPlaylistPublic = $isPlaylistPublic;

        return $this;
    }

    public function getIsLikesPublic(): bool
    {
        return $this->isLikesPublic;
    }

    public function setIsLikesPublic(bool $isLikesPublic): static
    {
        $this->isLikesPublic = $isLikesPublic;

        return $this;
    }

    public function getNotifNewFollower(): bool
    {
        return $this->notifNewFollower;
    }

    public function setNotifNewFollower(bool $notifNewFollower): static
    {
        $this->notifNewFollower = $notifNewFollower;

        return $this;
    }

    public function getNotifNewLike(): bool
    {
        return $this->notifNewLike;
    }

    public function setNotifNewLike(bool $notifNewLike): static
    {
        $this->notifNewLike = $notifNewLike;

        return $this;
    }

    public function getNotifNewComment(): bool
    {
        return $this->notifNewComment;
    }

    public function setNotifNewComment(bool $notifNewComment): static
    {
        $this->notifNewComment = $notifNewComment;

        return $this;
    }

    public function getNotifNewMessage(): bool
    {
        return $this->notifNewMessage;
    }

    public function setNotifNewMessage(bool $notifNewMessage): static
    {
        $this->notifNewMessage = $notifNewMessage;

        return $this;
    }
}
