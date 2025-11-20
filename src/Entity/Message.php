<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post as ApiPost;
use ApiPlatform\Metadata\Put;
use App\Entity\Interface\TimeStampableInterface;
use App\State\Message\MessageGetProvider;
use App\State\Message\MessageProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Message',
    operations: [
        new Get(
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_DETAIL], 'enable_max_depth' => true],
            provider: MessageGetProvider::class,
        ),
        new GetCollection(
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_READ], 'enable_max_depth' => true],
        ),
        new ApiPost(
            denormalizationContext: ['groups' => [self::SERIALIZATION_GROUP_WRITE]],
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_DETAIL], 'enable_max_depth' => true],
            processor: MessageProcessor::class,
            mercure: true
        ),
        new Put(
            denormalizationContext: ['groups' => [self::SERIALIZATION_GROUP_WRITE]],
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_DETAIL], 'enable_max_depth' => true],
            processor: MessageProcessor::class,
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
#[ORM\Table(name: 'message')]
class Message implements TimeStampableInterface
{
    use Trait\TimeStampableTrait;

    public const SERIALIZATION_GROUP_READ = 'message:read';
    public const SERIALIZATION_GROUP_DETAIL = 'message:detail';
    public const SERIALIZATION_GROUP_WRITE = 'message:write';

    public const TYPE_TEXT = 'text';
    public const TYPE_MUSIC = 'music';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
    ])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Conversation::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(name: 'conversation_id', referencedColumnName: 'id', nullable: false)]
    #[Groups([
        self::SERIALIZATION_GROUP_WRITE,
    ])]
    private Conversation $conversation;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'author_id', referencedColumnName: 'id', nullable: false)]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
    ])]
    private User $author;

    #[ORM\Column(name: 'type', type: 'string', length: 20)]
    #[Assert\Choice(choices: [self::TYPE_TEXT, self::TYPE_MUSIC], message: 'Choose a valid message type.')]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        self::SERIALIZATION_GROUP_WRITE,
    ])]
    private string $type = self::TYPE_TEXT;

    #[ORM\Column(name: 'content', type: 'text', nullable: true)]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        self::SERIALIZATION_GROUP_WRITE,
    ])]
    private ?string $content = null;

    // TODO: Change to Track Entity when implemented
    /**
     * @var array{
     *     platform: string,
     *     track_id: string,
     *     fallback_ids: array<string, string>
     * }|null
     */
    #[ORM\Column(name: 'track', type: 'json', nullable: true)]
    #[Groups([
        self::SERIALIZATION_GROUP_WRITE,
    ])]
    private ?array $track = null;

    // TODO: Remove when Track Entity implemented
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
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
    ])]
    private ?array $trackMetadata = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConversation(): Conversation
    {
        return $this->conversation;
    }

    public function setConversation(Conversation $conversation): static
    {
        $this->conversation = $conversation;

        return $this;
    }

    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
    ])]
    public function getConversationId(): ?int
    {
        return $this->conversation?->getId();
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
