<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post as ApiPost;
use App\ApiResource\Artist\ArtistCreateInput;
use App\ApiResource\Artist\ArtistUpdateInput;
use App\Entity\Interface\TimeStampableInterface;
use App\State\Artist\ArtistCreateProcessor;
use App\State\Artist\ArtistUpdateProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    shortName: 'Artist',
    operations: [
        new Get(
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_DETAIL], 'enable_max_depth' => true]
        ),
        new GetCollection(
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_READ], 'enable_max_depth' => true]
        ),
        new ApiPost(
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_DETAIL], 'enable_max_depth' => true],
            input: ArtistCreateInput::class,
            processor: ArtistCreateProcessor::class
        ),
        new Patch(
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_DETAIL], 'enable_max_depth' => true],
            input: ArtistUpdateInput::class,
            processor: ArtistUpdateProcessor::class
        ),
        new Delete(output: false),
    ]
)]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'artist')]
class Artist implements TimeStampableInterface
{
    use Trait\TimeStampableTrait;

    public const SERIALIZATION_GROUP_READ = 'artist:read';
    public const SERIALIZATION_GROUP_DETAIL = 'artist:detail';
    public const SERIALIZATION_GROUP_WRITE = 'artist:write';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        Track::SERIALIZATION_GROUP_READ,
        Track::SERIALIZATION_GROUP_DETAIL,
        Post::SERIALIZATION_GROUP_READ,
        Post::SERIALIZATION_GROUP_DETAIL,
    ])]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        self::SERIALIZATION_GROUP_WRITE,
        Track::SERIALIZATION_GROUP_READ,
        Track::SERIALIZATION_GROUP_DETAIL,
        Post::SERIALIZATION_GROUP_READ,
        Post::SERIALIZATION_GROUP_DETAIL,
    ])]
    private string $name;

    /**
     * @var Collection<int, ArtistSource>
     */
    #[ORM\OneToMany(targetEntity: ArtistSource::class, mappedBy: 'artist', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups([
        self::SERIALIZATION_GROUP_DETAIL,
    ])]
    private Collection $artistSources;

    /**
     * @var Collection<int, Track>
     */
    #[ORM\OneToMany(targetEntity: Track::class, mappedBy: 'artist')]
    private Collection $tracks;

    public function __construct()
    {
        $this->artistSources = new ArrayCollection();
        $this->tracks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, ArtistSource>
     */
    public function getArtistSources(): Collection
    {
        return $this->artistSources;
    }

    public function addArtistSource(ArtistSource $artistSource): static
    {
        if (!$this->artistSources->contains($artistSource)) {
            $this->artistSources->add($artistSource);
            $artistSource->setArtist($this);
        }

        return $this;
    }

    public function removeArtistSource(ArtistSource $artistSource): static
    {
        $this->artistSources->removeElement($artistSource);

        return $this;
    }

    /**
     * @return Collection<int, Track>
     */
    public function getTracks(): Collection
    {
        return $this->tracks;
    }

    public function addTrack(Track $track): static
    {
        if (!$this->tracks->contains($track)) {
            $this->tracks->add($track);
            $track->setArtist($this);
        }

        return $this;
    }

    public function removeTrack(Track $track): bool
    {
        return $this->tracks->removeElement($track);
    }
}
