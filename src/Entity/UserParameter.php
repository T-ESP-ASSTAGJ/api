<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use App\Entity\Enum\VisibilityEnum;
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

    #[ORM\Column(type: 'string', enumType: VisibilityEnum::class, options: ['default' => 'public'])]
    #[Groups([self::SERIALIZATION_GROUP_READ, self::SERIALIZATION_GROUP_WRITE, User::SERIALIZATION_GROUP_DETAIL])]
    private VisibilityEnum $followersVisibility = VisibilityEnum::Public;

    #[ORM\Column(type: 'string', enumType: VisibilityEnum::class, options: ['default' => 'public'])]
    #[Groups([self::SERIALIZATION_GROUP_READ, self::SERIALIZATION_GROUP_WRITE, User::SERIALIZATION_GROUP_DETAIL])]
    private VisibilityEnum $followingVisibility = VisibilityEnum::Public;

    #[ORM\Column(type: 'string', enumType: VisibilityEnum::class, options: ['default' => 'public'])]
    #[Groups([self::SERIALIZATION_GROUP_READ, self::SERIALIZATION_GROUP_WRITE, User::SERIALIZATION_GROUP_DETAIL])]
    private VisibilityEnum $statsVisibility = VisibilityEnum::Public;

    #[ORM\Column(type: 'string', enumType: VisibilityEnum::class, options: ['default' => 'public'])]
    #[Groups([self::SERIALIZATION_GROUP_READ, self::SERIALIZATION_GROUP_WRITE, User::SERIALIZATION_GROUP_DETAIL])]
    private VisibilityEnum $playlistVisibility = VisibilityEnum::Public;

    #[ORM\Column(type: 'string', enumType: VisibilityEnum::class, options: ['default' => 'public'])]
    #[Groups([self::SERIALIZATION_GROUP_READ, self::SERIALIZATION_GROUP_WRITE, User::SERIALIZATION_GROUP_DETAIL])]
    private VisibilityEnum $likesVisibility = VisibilityEnum::Public;

    #[ORM\Column(type: 'string', enumType: VisibilityEnum::class, options: ['default' => 'public'])]
    #[Groups([self::SERIALIZATION_GROUP_READ, self::SERIALIZATION_GROUP_WRITE, User::SERIALIZATION_GROUP_DETAIL])]
    private VisibilityEnum $notifNewFollower = VisibilityEnum::Public;

    #[ORM\Column(type: 'string', enumType: VisibilityEnum::class, options: ['default' => 'public'])]
    #[Groups([self::SERIALIZATION_GROUP_READ, self::SERIALIZATION_GROUP_WRITE, User::SERIALIZATION_GROUP_DETAIL])]
    private VisibilityEnum $notifNewLike = VisibilityEnum::Public;

    #[ORM\Column(type: 'string', enumType: VisibilityEnum::class, options: ['default' => 'public'])]
    #[Groups([self::SERIALIZATION_GROUP_READ, self::SERIALIZATION_GROUP_WRITE, User::SERIALIZATION_GROUP_DETAIL])]
    private VisibilityEnum $notifNewComment = VisibilityEnum::Public;

    #[ORM\Column(type: 'string', enumType: VisibilityEnum::class, options: ['default' => 'public'])]
    #[Groups([self::SERIALIZATION_GROUP_READ, self::SERIALIZATION_GROUP_WRITE, User::SERIALIZATION_GROUP_DETAIL])]
    private VisibilityEnum $notifNewMessage = VisibilityEnum::Public;

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

    public function getFollowersVisibility(): VisibilityEnum
    {
        return $this->followersVisibility;
    }

    public function setFollowersVisibility(VisibilityEnum $followersVisibility): static
    {
        $this->followersVisibility = $followersVisibility;

        return $this;
    }

    public function getFollowingVisibility(): VisibilityEnum
    {
        return $this->followingVisibility;
    }

    public function setFollowingVisibility(VisibilityEnum $followingVisibility): static
    {
        $this->followingVisibility = $followingVisibility;

        return $this;
    }

    public function getStatsVisibility(): VisibilityEnum
    {
        return $this->statsVisibility;
    }

    public function setStatsVisibility(VisibilityEnum $statsVisibility): static
    {
        $this->statsVisibility = $statsVisibility;

        return $this;
    }

    public function getPlaylistVisibility(): VisibilityEnum
    {
        return $this->playlistVisibility;
    }

    public function setPlaylistVisibility(VisibilityEnum $playlistVisibility): static
    {
        $this->playlistVisibility = $playlistVisibility;

        return $this;
    }

    public function getLikesVisibility(): VisibilityEnum
    {
        return $this->likesVisibility;
    }

    public function setLikesVisibility(VisibilityEnum $likesVisibility): static
    {
        $this->likesVisibility = $likesVisibility;

        return $this;
    }

    public function getNotifNewFollower(): VisibilityEnum
    {
        return $this->notifNewFollower;
    }

    public function setNotifNewFollower(VisibilityEnum $notifNewFollower): static
    {
        $this->notifNewFollower = $notifNewFollower;

        return $this;
    }

    public function getNotifNewLike(): VisibilityEnum
    {
        return $this->notifNewLike;
    }

    public function setNotifNewLike(VisibilityEnum $notifNewLike): static
    {
        $this->notifNewLike = $notifNewLike;

        return $this;
    }

    public function getNotifNewComment(): VisibilityEnum
    {
        return $this->notifNewComment;
    }

    public function setNotifNewComment(VisibilityEnum $notifNewComment): static
    {
        $this->notifNewComment = $notifNewComment;

        return $this;
    }

    public function getNotifNewMessage(): VisibilityEnum
    {
        return $this->notifNewMessage;
    }

    public function setNotifNewMessage(VisibilityEnum $notifNewMessage): static
    {
        $this->notifNewMessage = $notifNewMessage;

        return $this;
    }
}
