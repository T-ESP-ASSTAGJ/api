<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post as ApiPost;
use App\ApiResource\Track\TrackCreateInput;
use App\ApiResource\Track\TrackUpdateInput;
use App\Entity\Interface\TimeStampableInterface;
use App\State\Track\TrackCreateProcessor;
use App\State\Track\TrackUpdateProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    shortName: 'Track',
    operations: [
        new Get(
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_DETAIL]]
        ),
        new GetCollection(
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_READ]]
        ),
        new ApiPost(
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_DETAIL]],
            input: TrackCreateInput::class,
            processor: TrackCreateProcessor::class
        ),
        new Patch(
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_DETAIL]],
            input: TrackUpdateInput::class,
            processor: TrackUpdateProcessor::class
        ),
        new Delete(output: false),
    ]
)]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'track')]
class Track implements TimeStampableInterface
{
    use Trait\TimeStampableTrait;

    public const SERIALIZATION_GROUP_READ = 'track:read';
    public const SERIALIZATION_GROUP_DETAIL = 'track:detail';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        Post::SERIALIZATION_GROUP_DETAIL,
        Post::SERIALIZATION_GROUP_READ,
    ])]
    private ?int $id = null;

    #[ORM\Column(name: 'title', type: 'string', length: 255)]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        Post::SERIALIZATION_GROUP_DETAIL,
        Post::SERIALIZATION_GROUP_READ,
    ])]
    private string $title;

    #[ORM\Column(name: 'cover_url', type: 'text', nullable: true)]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        Post::SERIALIZATION_GROUP_DETAIL,
        Post::SERIALIZATION_GROUP_READ,
    ])]
    private ?string $coverUrl = null;

    /**
     * @var array<string, mixed>
     */
    #[ORM\Column(name: 'metadata', type: 'json')]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        Post::SERIALIZATION_GROUP_DETAIL,
        Post::SERIALIZATION_GROUP_READ,
    ])]
    private array $metadata = [];

    #[ORM\ManyToOne(targetEntity: Artist::class, inversedBy: 'tracks')]
    #[ORM\JoinColumn(name: 'artist_id', referencedColumnName: 'id', nullable: false)]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        Post::SERIALIZATION_GROUP_DETAIL,
        Post::SERIALIZATION_GROUP_READ,
    ])]
    private Artist $artist;

    /**
     * @var Collection<int, TrackSource>
     */
    #[ORM\OneToMany(targetEntity: TrackSource::class, mappedBy: 'track', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups([
        self::SERIALIZATION_GROUP_DETAIL,
    ])]
    private Collection $trackSources;

    public function __construct()
    {
        $this->trackSources = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getCoverUrl(): ?string
    {
        return $this->coverUrl;
    }

    public function setCoverUrl(?string $coverUrl): static
    {
        $this->coverUrl = $coverUrl;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function setMetadata(array $metadata): static
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getArtist(): Artist
    {
        return $this->artist;
    }

    public function setArtist(Artist $artist): static
    {
        $this->artist = $artist;

        return $this;
    }

    /**
     * @return Collection<int, TrackSource>
     */
    public function getTrackSources(): Collection
    {
        return $this->trackSources;
    }

    public function addTrackSource(TrackSource $trackSource): static
    {
        if (!$this->trackSources->contains($trackSource)) {
            $this->trackSources->add($trackSource);
            $trackSource->setTrack($this);
        }

        return $this;
    }

    public function removeTrackSource(TrackSource $trackSource): static
    {
        $this->trackSources->removeElement($trackSource);

        return $this;
    }
}
