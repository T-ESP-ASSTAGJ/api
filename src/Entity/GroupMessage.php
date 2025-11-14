<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post as ApiPost;
use ApiPlatform\Metadata\Put;
use App\ApiResource\GroupMessage\GroupMessageCreateInput;
use App\ApiResource\GroupMessage\GroupMessageGetOutput;
use App\Entity\Interface\TimeStampableInterface;
use App\State\GroupMessage\GroupMessageCreateProcessor;
use App\State\GroupMessage\GroupMessageGetProvider;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'GroupMessage',
    operations: [
        new Get(
            output: GroupMessageGetOutput::class,
            provider: GroupMessageGetProvider::class
        ),
        new GetCollection(
            output: GroupMessageGetOutput::class
        ),
        new ApiPost(
            input: GroupMessageCreateInput::class,
            output: GroupMessageGetOutput::class,
            processor: GroupMessageCreateProcessor::class,
            mercure: true
        ),
        new Put(
            output: GroupMessageGetOutput::class,
            mercure: true
        ),
        new Delete(
            output: false,
            mercure: true
        ),
    ],
    mercure: true
)]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'group_message')]
class GroupMessage implements TimeStampableInterface
{
    use Trait\TimeStampableTrait;

    public const TYPE_TEXT = 'text';
    public const TYPE_MUSIC = 'music';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'group_id', type: 'integer')]
    private int $groupId;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'author_id', referencedColumnName: 'id', nullable: false)]
    private User $author;

    #[ORM\Column(name: 'type', type: 'string', length: 20)]
    #[Assert\Choice(choices: [self::TYPE_TEXT, self::TYPE_MUSIC], message: 'Choose a valid message type.')]
    private string $type = self::TYPE_TEXT;

    #[ORM\Column(name: 'content', type: 'text', nullable: true)]
    private ?string $content = null;

    /**
     * @var array{
     *     platform: string,
     *     track_id: string,
     *     fallback_ids: array<string, string>
     * }|null
     */
    #[ORM\Column(name: 'track', type: 'json', nullable: true)]
    private ?array $track = null;

    /**
     * @var array{
     *     title: string,
     *     artist: string,
     *     album_cover: string,
     *     preview_url: string|null,
     *     platform_link: string,
     *     availability: string
     * }|null
     */
    #[ORM\Column(name: 'track_metadata', type: 'json', nullable: true)]
    private ?array $trackMetadata = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroupId(): int
    {
        return $this->groupId;
    }

    public function setGroupId(int $groupId): static
    {
        $this->groupId = $groupId;

        return $this;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function setAuthor(User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return array{
     *     platform: string,
     *     track_id: string,
     *     fallback_ids: array<string, string>
     * }|null
     */
    public function getTrack(): ?array
    {
        return $this->track;
    }

    /**
     * @param array{
     *     platform: string,
     *     track_id: string,
     *     fallback_ids: array<string, string>
     * }|null $track
     */
    public function setTrack(?array $track): static
    {
        $this->track = $track;

        return $this;
    }

    /**
     * @return array{
     *     title: string,
     *     artist: string,
     *     album_cover: string,
     *     preview_url: string|null,
     *     platform_link: string,
     *     availability: string
     * }|null
     */
    public function getTrackMetadata(): ?array
    {
        return $this->trackMetadata;
    }

    /**
     * @param array{
     *     title: string,
     *     artist: string,
     *     album_cover: string,
     *     preview_url: string|null,
     *     platform_link: string,
     *     availability: string
     * }|null $trackMetadata
     */
    public function setTrackMetadata(?array $trackMetadata): static
    {
        $this->trackMetadata = $trackMetadata;

        return $this;
    }

    public function isMusicMessage(): bool
    {
        return self::TYPE_MUSIC === $this->type;
    }
}
