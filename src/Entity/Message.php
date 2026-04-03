<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post as ApiPost;
use ApiPlatform\Metadata\Put;
use App\ApiResource\Message\MessageCreateInput;
use App\ApiResource\Message\MessageUpdateInput;
use App\Entity\Interface\TimeStampableInterface;
use App\State\IsReadProvider;
use App\State\Message\MessageProcessor;
use App\State\Message\MessageUpdateProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Message',
    operations: [
        new Get(
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_DETAIL]],
            provider: IsReadProvider::class,
        ),
        new GetCollection(
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_READ]],
            provider: IsReadProvider::class,
        ),
        new ApiPost(
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_DETAIL]],
            input: MessageCreateInput::class,
            processor: MessageProcessor::class,
        ),
        new Put(
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_DETAIL]],
            denormalizationContext: ['groups' => [self::SERIALIZATION_GROUP_UPDATE]],
            input: MessageUpdateInput::class,
            provider: IsReadProvider::class,
            processor: MessageUpdateProcessor::class
        ),
        new Delete(
            output: false,
        ),
    ],
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
    public const SERIALIZATION_GROUP_UPDATE = 'message:update';

    public const TYPE_TEXT = 'text';
    public const TYPE_MUSIC = 'music';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        Conversation::SERIALIZATION_GROUP_DETAIL,
        Conversation::SERIALIZATION_GROUP_READ,
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
        Conversation::SERIALIZATION_GROUP_DETAIL,
        Conversation::SERIALIZATION_GROUP_READ,
    ])]
    private User $author;

    #[ORM\Column(name: 'type', type: 'string', length: 20)]
    #[Assert\Choice(choices: [self::TYPE_TEXT, self::TYPE_MUSIC], message: 'Choose a valid message type.')]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        self::SERIALIZATION_GROUP_WRITE,
        Conversation::SERIALIZATION_GROUP_DETAIL,
    ])]
    private string $type = self::TYPE_TEXT;

    #[ORM\Column(name: 'content', type: 'text', nullable: true)]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        self::SERIALIZATION_GROUP_WRITE,
        self::SERIALIZATION_GROUP_UPDATE,
        Conversation::SERIALIZATION_GROUP_DETAIL,
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
        Conversation::SERIALIZATION_GROUP_DETAIL,
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

    #[ORM\Column(name: 'is_read', type: 'boolean', options: ['default' => false])]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        Conversation::SERIALIZATION_GROUP_READ,
    ])]
    private bool $isRead = false;

    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        Conversation::SERIALIZATION_GROUP_READ,
    ])]
    private ?bool $read = null;

    #[ORM\Column(name: 'read_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
    ])]
    private ?\DateTimeImmutable $readAt = null;

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
    public function getConversationId(): int
    {
        return $this->conversation->getId();
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

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): static
    {
        $this->isRead = $isRead;

        return $this;
    }

    public function getRead(): ?bool
    {
        return $this->read;
    }

    public function setRead(?bool $read): static
    {
        $this->read = $read;

        return $this;
    }

    public function getReadAt(): ?\DateTimeImmutable
    {
        return $this->readAt;
    }

    public function setReadAt(?\DateTimeImmutable $readAt): static
    {
        $this->readAt = $readAt;

        return $this;
    }

    public function markAsRead(): static
    {
        $this->isRead = true;
        $this->readAt = new \DateTimeImmutable();

        return $this;
    }

    #[Groups([
        Conversation::SERIALIZATION_GROUP_READ,
    ])]
    public function getMessagePreview(): string
    {
        if ($this->isMusicMessage()) {
            return 'Vous a partagé une musique';
        }

        return $this->getContent() ?? '';
    }
}
