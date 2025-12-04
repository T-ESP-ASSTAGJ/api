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
use App\ApiResource\Post\PostGetOutput;
use App\Entity\Interface\TimeStampableInterface;
use App\State\Post\PostCreateProcessor;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    shortName: 'Post',
    operations: [
        new Get(output: PostGetOutput::class),
        new GetCollection(output: PostGetOutput::class),
        new ApiPost(
            input: PostCreateInput::class,
            output: PostGetOutput::class,
            processor: PostCreateProcessor::class
        ),
        new Put(output: PostGetOutput::class),
        new Delete(output: false),
    ]
)]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'post')]
class Post implements TimeStampableInterface
{
    use Trait\TimeStampableTrait;

    public const SERIALIZATION_GROUP_READ = 'post:read';
    public const SERIALIZATION_GROUP_DETAIL = 'post:detail';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'user_id', type: 'integer')]
    private int $userId;

    #[ORM\Column(name: 'song_preview_url', type: 'string', length: 500, nullable: true)]
    private ?string $songPreviewUrl = null;

    #[ORM\Column(name: 'caption', type: 'text', nullable: true)]
    private ?string $caption = null;

    #[ORM\Column(name: 'track_id', type: 'integer')]
    private int $trackId;

    #[ORM\ManyToOne(targetEntity: Track::class)]
    #[ORM\JoinColumn(name: 'track_id', referencedColumnName: 'id', nullable: false)]
    private Track $track;

    #[ORM\Column(name: 'photo_url', type: 'string', length: 500, nullable: true)]
    private ?string $photoUrl = null;

    #[ORM\Column(name: 'location', type: 'string', length: 255, nullable: true)]
    private ?string $location = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getSongPreviewUrl(): ?string
    {
        return $this->songPreviewUrl;
    }

    public function setSongPreviewUrl(?string $songPreviewUrl): static
    {
        $this->songPreviewUrl = $songPreviewUrl;

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

    public function getTrackId(): int
    {
        return $this->trackId;
    }

    public function setTrackId(int $trackId): static
    {
        $this->trackId = $trackId;

        return $this;
    }

    public function getTrack(): Track
    {
        return $this->track;
    }

    public function setTrack(Track $track): static
    {
        $this->track = $track;
        $this->trackId = $track->getId();

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
}
