<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\MusicPlatformEnum;
use App\Entity\Interface\TimeStampableInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @deprecated This entity is deprecated and will be removed in a future version.
 *             Artist sources are no longer needed with Apple Music integration.
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'artist_source')]
#[ORM\UniqueConstraint(name: 'unique_artist_platform', columns: ['artist_id', 'platform', 'platform_artist_id'])]
class ArtistSource implements TimeStampableInterface
{
    use Trait\TimeStampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[Groups([
        Artist::SERIALIZATION_GROUP_READ,
        Artist::SERIALIZATION_GROUP_DETAIL]
    )]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Artist::class, inversedBy: 'artistSources')]
    #[ORM\JoinColumn(name: 'artist_id', nullable: false, onDelete: 'CASCADE')]
    private Artist $artist;

    #[Assert\Choice(callback: [MusicPlatformEnum::class, 'values'])]
    #[ORM\Column(name: 'platform', type: 'string', length: 50)]
    #[Groups([
        Artist::SERIALIZATION_GROUP_READ,
        Artist::SERIALIZATION_GROUP_DETAIL,
        Artist::SERIALIZATION_GROUP_WRITE,
    ])]
    private string $platform;

    #[ORM\Column(name: 'platform_artist_id', type: 'string', length: 255)]
    #[Groups([
        Artist::SERIALIZATION_GROUP_READ,
        Artist::SERIALIZATION_GROUP_DETAIL,
        Artist::SERIALIZATION_GROUP_WRITE]
    )]
    private string $platformArtistId;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPlatform(): string
    {
        return $this->platform;
    }

    public function setPlatform(string $platform): static
    {
        $this->platform = $platform;

        return $this;
    }

    public function getPlatformArtistId(): string
    {
        return $this->platformArtistId;
    }

    public function setPlatformArtistId(string $platformArtistId): static
    {
        $this->platformArtistId = $platformArtistId;

        return $this;
    }
}
