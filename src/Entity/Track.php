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
use App\ApiResource\Track\TrackGetOutput;
use App\ApiResource\Track\TrackUpdateInput;
use App\Entity\Interface\TimeStampableInterface;
use App\State\Track\TrackCreateProcessor;
use App\State\Track\TrackUpdateProcessor;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    shortName: 'Track',
    operations: [
        new Get(output: TrackGetOutput::class),
        new GetCollection(output: TrackGetOutput::class),
        new ApiPost(
            input: TrackCreateInput::class,
            output: TrackGetOutput::class,
            processor: TrackCreateProcessor::class
        ),
        new Patch(
            input: TrackUpdateInput::class,
            output: TrackGetOutput::class,
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

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'title', type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(name: 'cover_url', type: 'text', nullable: true)]
    private ?string $coverUrl = null;

    /**
     * @var array<string, mixed>
     */
    #[ORM\Column(name: 'metadata', type: 'json')]
    private array $metadata = [];

    #[ORM\Column(name: 'artist_id', type: 'integer')]
    private int $artistId;

    #[ORM\ManyToOne(targetEntity: Artist::class, inversedBy: 'tracks')]
    #[ORM\JoinColumn(name: 'artist_id', referencedColumnName: 'id', nullable: true)]
    private ?Artist $artist = null;

    #[ORM\Column(name: 'length', type: 'integer')]
    private int $length;

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

    public function getArtistId(): int
    {
        return $this->artistId;
    }

    public function setArtistId(int $artistId): static
    {
        $this->artistId = $artistId;

        return $this;
    }

    public function getArtist(): ?Artist
    {
        return $this->artist;
    }

    public function setArtist(?Artist $artist): static
    {
        $this->artist = $artist;
        $this->artistId = $artist?->getId();

        return $this;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function setLength(int $length): static
    {
        $this->length = $length;

        return $this;
    }
}
