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
use App\Entity\Interface\LikeableInterface;
use App\Entity\Interface\TimeStampableInterface;
use App\State\IsLikedProvider;
use App\State\Post\PostCreateProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    shortName: 'Post',
    operations: [
        new Get(
            normalizationContext: ['groups' => [
                self::SERIALIZATION_GROUP_DETAIL,
                User::SERIALIZATION_GROUP_READ,
                Artist::SERIALIZATION_GROUP_READ,
                self::LIKE_SERIALIZATION_GROUP_READ,
            ]],
            provider: IsLikedProvider::class
        ),
        new GetCollection(
            normalizationContext: ['groups' => [
                self::SERIALIZATION_GROUP_READ,
                User::SERIALIZATION_GROUP_READ,
                Artist::SERIALIZATION_GROUP_READ,
                self::LIKE_SERIALIZATION_GROUP_READ,
            ]],
            provider: IsLikedProvider::class
        ),
        new ApiPost(
            normalizationContext: ['groups' => [
                self::SERIALIZATION_GROUP_DETAIL,
                User::SERIALIZATION_GROUP_READ,
                Artist::SERIALIZATION_GROUP_READ,
            ]],
            input: PostCreateInput::class,
            processor: PostCreateProcessor::class
        ),
        new Put(
            normalizationContext: ['groups' => [
                self::SERIALIZATION_GROUP_DETAIL,
                User::SERIALIZATION_GROUP_READ,
                Artist::SERIALIZATION_GROUP_READ,
            ]],
            security: 'object.getUser() == user',
        ),
        new Delete(
            security: 'object.getUser() == user',
            output: false
        ),
    ]
)]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'post')]
class Post implements LikeableInterface, TimeStampableInterface
{
    use Trait\LikeableTrait;
    use Trait\TimeStampableTrait;

    public const SERIALIZATION_GROUP_READ = 'post:read';
    public const SERIALIZATION_GROUP_DETAIL = 'post:detail';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
    ])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
    ])]
    private User $user;

    #[ORM\Column(name: 'caption', type: 'text', nullable: true)]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
    ])]
    private ?string $caption = null;

    #[ORM\ManyToOne(targetEntity: Track::class)]
    #[ORM\JoinColumn(name: 'track_id', referencedColumnName: 'id', nullable: false)]
    #[Groups([
        self::SERIALIZATION_GROUP_DETAIL,
    ])]
    private Track $track;

    #[ORM\Column(name: 'photo_url', type: 'string', length: 500, nullable: true)]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
    ])]
    private ?string $photoUrl = null;

    #[ORM\Column(name: 'location', type: 'string', length: 255, nullable: true)]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
    ])]
    private ?string $location = null;

    #[ORM\Column(name: 'comments_count', type: 'integer', options: ['default' => 0])]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
    ])]
    private int $commentsCount = 0;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'post', cascade: ['remove'], orphanRemoval: true)]
    private Collection $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

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

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function setCaption(?string $caption): static
    {
        $this->caption = $caption;

        return $this;
    }

    public function getTrack(): Track
    {
        return $this->track;
    }

    public function setTrack(Track $track): static
    {
        $this->track = $track;

        return $this;
    }

    public function getPhotoUrl(): ?string
    {
        return $this->photoUrl;
    }

    public function setPhotoUrl(?string $photoUrl): static
    {
        $this->photoUrl = $photoUrl;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getCommentsCount(): int
    {
        return $this->commentsCount;
    }

    public function updateCommentsCount(): static
    {
        $this->commentsCount = count($this->comments);

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        $this->comments->removeElement($comment);

        return $this;
    }
}
