<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interface\TimeStampableInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'track_source')]
#[ORM\UniqueConstraint(name: 'unique_track_platform', columns: ['track_id', 'platform', 'platform_track_id'])]
class TrackSource implements TimeStampableInterface
{
    use Trait\TimeStampableTrait;

    public const PLATFORM_SPOTIFY = 'spotify';
    public const PLATFORM_DEEZER = 'deezer';
    public const PLATFORM_SOUNDCLOUD = 'soundcloud';
    public const PLATFORM_APPLE_MUSIC = 'apple_music';

    public const PLATFORMS = [
        self::PLATFORM_SPOTIFY,
        self::PLATFORM_DEEZER,
        self::PLATFORM_SOUNDCLOUD,
        self::PLATFORM_APPLE_MUSIC,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[Groups([
        Track::SERIALIZATION_GROUP_READ,
        Track::SERIALIZATION_GROUP_DETAIL,
    ])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Track::class, inversedBy: 'trackSources')]
    #[ORM\JoinColumn(name: 'track_id', nullable: false, onDelete: 'CASCADE')]
    private Track $track;

    #[ORM\Column(name: 'platform', type: 'string', length: 50)]
    #[Groups([
        Track::SERIALIZATION_GROUP_READ,
        Track::SERIALIZATION_GROUP_DETAIL,
    ])]
    private string $platform;

    #[ORM\Column(name: 'platform_track_id', type: 'string', length: 255)]
    #[Groups([
        Track::SERIALIZATION_GROUP_READ,
        Track::SERIALIZATION_GROUP_DETAIL,
    ])]
    private string $platformTrackId;

    /**
     * @var array<string, mixed> Contains: popularity, rank, explicit, preview_url
     */
    #[ORM\Column(name: 'metadata', type: 'json')]
    #[Groups([
        Track::SERIALIZATION_GROUP_READ,
        Track::SERIALIZATION_GROUP_DETAIL,
    ])]
    private array $metadata = [];

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPlatform(): string
    {
        return $this->platform;
    }

    public function setPlatform(string $platform): static
    {
        if (!\in_array($platform, self::PLATFORMS, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid platform "%s". Allowed platforms: %s', $platform, implode(', ', self::PLATFORMS)));
        }

        $this->platform = $platform;

        return $this;
    }

    public function getPlatformTrackId(): string
    {
        return $this->platformTrackId;
    }

    public function setPlatformTrackId(string $platformTrackId): static
    {
        $this->platformTrackId = $platformTrackId;

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
}
