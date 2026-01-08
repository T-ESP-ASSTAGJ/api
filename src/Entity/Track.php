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

    #[ORM\Column(name: 'song_id', type: 'string', length: 255)]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        Post::SERIALIZATION_GROUP_DETAIL,
        Post::SERIALIZATION_GROUP_READ,
    ])]
    private string $songId;

    #[ORM\Column(name: 'title', type: 'string', length: 255)]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        Post::SERIALIZATION_GROUP_DETAIL,
        Post::SERIALIZATION_GROUP_READ,
    ])]
    private string $title;

    #[ORM\Column(name: 'artist_name', type: 'string', length: 255)]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        Post::SERIALIZATION_GROUP_DETAIL,
        Post::SERIALIZATION_GROUP_READ,
    ])]
    private string $artistName;

    #[ORM\Column(name: 'release_year', type: 'integer', nullable: true)]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        Post::SERIALIZATION_GROUP_DETAIL,
        Post::SERIALIZATION_GROUP_READ,
    ])]
    private ?int $releaseYear = null;

    #[ORM\Column(length: 300, nullable: true)]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        Post::SERIALIZATION_GROUP_DETAIL,
        Post::SERIALIZATION_GROUP_READ,
    ])]
    private ?string $coverImage = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSongId(): string
    {
        return $this->songId;
    }

    public function setSongId(string $songId): static
    {
        $this->songId = $songId;

        return $this;
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

    public function getArtistName(): string
    {
        return $this->artistName;
    }

    public function setArtistName(string $artistName): static
    {
        $this->artistName = $artistName;

        return $this;
    }

    public function getReleaseYear(): ?int
    {
        return $this->releaseYear;
    }

    public function setReleaseYear(?int $releaseYear): static
    {
        $this->releaseYear = $releaseYear;

        return $this;
    }

    public function getCoverImage(): ?string
    {
        return $this->coverImage;
    }

    public function setCoverImage(?string $coverImage): static
    {
        $this->coverImage = $coverImage;

        return $this;
    }
}
