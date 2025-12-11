<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Put;
use App\ApiResource\User\UserPutInput;
use App\Entity\Interface\TimeStampableInterface;
use App\Repository\UserRepository;
use App\State\User\UserFollowersProvider;
use App\State\User\UserFollowingProvider;
use App\State\User\UserLikesProvider;
use App\State\User\UserMeProvider;
use App\State\User\UserPutProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'User',
    operations: [
        new Get(
            uriTemplate: '/users/me',
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_DETAIL]],
            provider: UserMeProvider::class,
        ),
        new GetCollection(
            uriTemplate: '/users/{id}/followers',
            provider: UserFollowersProvider::class,
        ),
        new GetCollection(
            uriTemplate: '/users/{id}/following',
            provider: UserFollowingProvider::class,
        ),
        new GetCollection(
            uriTemplate: '/users/{id}/likes',
            normalizationContext: ['groups' => [Like::SERIALIZATION_GROUP_READ, Post::SERIALIZATION_GROUP_READ]],
            provider: UserLikesProvider::class,
        ),
        new Get(
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_DETAIL]],
        ),
        new GetCollection(
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_READ]],
        ),
        new Put(
            uriTemplate: '/users/me',
            input: UserPutInput::class,
            processor: UserPutProcessor::class
        ),
        new Delete(
            security: "is_granted('ROLE_USER') and object == user",
            output: false
        ),
    ],
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, TimeStampableInterface
{
    use Trait\TimeStampableTrait;

    public const SERIALIZATION_GROUP_READ = 'user:read';
    public const SERIALIZATION_GROUP_DETAIL = 'user:detail';
    public const SERIALIZATION_GROUP_WRITE = 'user:write';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        Follow::SERIALIZATION_GROUP_DETAIL,
        Message::SERIALIZATION_GROUP_READ,
        Message::SERIALIZATION_GROUP_DETAIL,
        Post::SERIALIZATION_GROUP_READ,
        Post::SERIALIZATION_GROUP_DETAIL,
    ])]
    private ?int $id = null;

    #[ORM\Column(name: 'username', type: 'string', length: 180, unique: true, nullable: true)]
    #[Assert\Length(min: 3, max: 180)]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        self::SERIALIZATION_GROUP_WRITE,
        Follow::SERIALIZATION_GROUP_DETAIL,
        Message::SERIALIZATION_GROUP_READ,
        Message::SERIALIZATION_GROUP_DETAIL,
        Post::SERIALIZATION_GROUP_READ,
        Post::SERIALIZATION_GROUP_DETAIL,
    ])]
    private ?string $username = null;

    #[ORM\Column(name: 'email', type: 'string', length: 180, unique: true)]
    #[Assert\Email]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
    ])]
    private string $email;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column(name: 'roles', type: 'json')]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
    ])]
    private array $roles = [];

    #[ORM\Column(name: 'password', type: 'string', length: 255, nullable: true)]
    private string $password;

    #[ORM\Column(name: 'phone_number', type: 'string', length: 20, unique: true, nullable: true)]
    #[Assert\Regex('/\+?\d+/')]
    private string $phoneNumber;

    #[ORM\Column(name: 'profile_picture', type: 'string', length: 255, nullable: true)]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        self::SERIALIZATION_GROUP_WRITE,
        Follow::SERIALIZATION_GROUP_DETAIL,
        Message::SERIALIZATION_GROUP_READ,
        Message::SERIALIZATION_GROUP_DETAIL,
        Post::SERIALIZATION_GROUP_READ,
        Post::SERIALIZATION_GROUP_DETAIL,
    ])]
    private ?string $profilePicture = null;

    #[ORM\Column(name: 'bio', type: 'string', length: 255, nullable: true)]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        self::SERIALIZATION_GROUP_WRITE,
    ])]
    private ?string $bio = null;

    #[ORM\Column(name: 'is_verified', type: 'boolean', options: ['default' => false])]
    #[Groups([
        self::SERIALIZATION_GROUP_DETAIL,
    ])]
    private bool $isVerified = false;

    #[ORM\Column(name: 'needs_profile', type: 'boolean', options: ['default' => true])]
    private bool $needsProfile = true;

    // List of users THIS USER follows
    /** @var Collection<Follow> */
    #[Ignore]
    #[ORM\OneToMany(targetEntity: Follow::class, mappedBy: 'follower')]
    private Collection $following;

    // List of users WHO FOLLOW this user
    /** @var Collection<Follow> */
    #[Ignore]
    #[ORM\OneToMany(targetEntity: Follow::class, mappedBy: 'followedUser')]
    private Collection $followers;

    #[ORM\OneToMany(targetEntity: Like::class, mappedBy: 'user')]
    private Collection $likes;

    public function __construct()
    {
        $this->following = new ArrayCollection();
        $this->followers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(?string $profilePicture): static
    {
        $this->profilePicture = $profilePicture;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;

        return $this;
    }

    public function getIsVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getNeedsProfile(): bool
    {
        return $this->needsProfile;
    }

    public function setNeedsProfile(bool $needsProfile): static
    {
        $this->needsProfile = $needsProfile;

        return $this;
    }

    #[Groups([self::SERIALIZATION_GROUP_DETAIL])]
    public function getFollowingCount(): int
    {
        return $this->following->count();
    }

    #[Groups([self::SERIALIZATION_GROUP_DETAIL])]
    public function getFollowersCount(): int
    {
        return $this->followers->count();
    }

    /** @return array<int, array{id: int|null, username: string|null}> */
    #[Groups([self::SERIALIZATION_GROUP_DETAIL])]
    public function getFollowing(): array
    {
        return $this->following->map(fn (Follow $follow) => [
            'id' => $follow->getFollowedUser()?->getId(),
            'username' => $follow->getFollowedUser()?->getUsername(),
            'profilePicture' => $follow->getFollowedUser()?->getProfilePicture(),
        ])->toArray();
    }

    /** @return array<int, array{id: int|null, username: string|null}> */
    #[Groups([self::SERIALIZATION_GROUP_DETAIL])]
    public function getFollowers(): array
    {
        return $this->followers->map(fn (Follow $follow) => [
            'id' => $follow->getFollower()?->getId(),
            'username' => $follow->getFollower()?->getUsername(),
            'profilePicture' => $follow->getFollower()?->getProfilePicture(),
        ])->toArray();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        if (empty($this->roles)) {
            $this->roles = ['ROLE_USER'];
        }
    }

    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
    }
}
