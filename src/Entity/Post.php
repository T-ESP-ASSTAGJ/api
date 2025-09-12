<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post as ApiPost;
use ApiPlatform\Metadata\Put;
use App\DTO\Post\PostCreateInput;
use App\DTO\Post\PostGetOutput;
use App\Entity\Interface\TimeStampableInterface;
use App\Processor\Post\PostCreateProcessor;
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

    /**
     * @var array{
     *     title: string,
     *     artist: string,
     *     album: string,
     *     duration: int,
     *     genre: string,
     *     platform: string,
     *     platform_id: string,
     *     external_url: string,
     *     isrc: string
     * }
     */
    #[ORM\Column(name: 'track', type: 'json')]
    private array $track;

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

    /**
     * @return array{
     *     title: string,
     *     artist: string,
     *     album: string,
     *     duration: int,
     *     genre: string,
     *     platform: string,
     *     platform_id: string,
     *     external_url: string,
     *     isrc: string
     * }
     */
    public function getTrack(): array
    {
        return $this->track;
    }

    /**
     * @param array{
     *     title: string,
     *     artist: string,
     *     album: string,
     *     duration: int,
     *     genre: string,
     *     platform: string,
     *     platform_id: string,
     *     external_url: string,
     *     isrc: string
     * } $track
     */
    public function setTrack(array $track): static
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
}
