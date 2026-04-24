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
use App\Entity\Enum\MessageTypeEnum;
use App\Entity\Interface\TimeStampableInterface;
use App\State\Message\MessageProcessor;
use App\State\Message\MessageUpdateProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Message',
    operations: [
        new Get(
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_DETAIL]],
        ),
        new GetCollection(
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_READ]],
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
            processor: MessageUpdateProcessor::class,
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
    public const SERIALIZATION_GROUP_MERCURE = 'message:mercure';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        self::SERIALIZATION_GROUP_MERCURE,
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
    #[ORM\JoinColumn(name: 'author_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        self::SERIALIZATION_GROUP_MERCURE,
        Conversation::SERIALIZATION_GROUP_DETAIL,
        Conversation::SERIALIZATION_GROUP_READ,
    ])]
    private User $author;

    #[ORM\Column(name: 'type', type: 'string', length: 20, enumType: MessageTypeEnum::class)]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        self::SERIALIZATION_GROUP_WRITE,
        self::SERIALIZATION_GROUP_MERCURE,
        Conversation::SERIALIZATION_GROUP_DETAIL,
    ])]
    private MessageTypeEnum $type = MessageTypeEnum::Text;

    #[ORM\Column(name: 'content', type: 'text', nullable: true)]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        self::SERIALIZATION_GROUP_WRITE,
        self::SERIALIZATION_GROUP_UPDATE,
        self::SERIALIZATION_GROUP_MERCURE,
        Conversation::SERIALIZATION_GROUP_DETAIL,
    ])]
    private ?string $content = null;

    #[ORM\ManyToOne(targetEntity: Track::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Assert\NotNull(message: 'A music message must have a track.', groups: ['music'])]
    #[Groups([
        self::SERIALIZATION_GROUP_WRITE,
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        self::SERIALIZATION_GROUP_MERCURE,
        Conversation::SERIALIZATION_GROUP_DETAIL,
    ])]
    private ?Track $track = null;

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
        self::SERIALIZATION_GROUP_MERCURE,
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

    public function getType(): MessageTypeEnum
    {
        return $this->type;
    }

    public function setType(MessageTypeEnum $type): static
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

    public function getTrack(): ?Track
    {
        return $this->track;
    }

    public function setTrack(?Track $track): static
    {
        $this->track = $track;

        return $this;
    }

    public function isMusicMessage(): bool
    {
        return MessageTypeEnum::Music === $this->type;
    }

    #[Groups([
        Conversation::SERIALIZATION_GROUP_READ,
    ])]
    public function getMessagePreview(): string
    {
        if ($this->isMusicMessage()) {
            return 'Just shared a track';
        }

        return $this->getContent() ?? '';
    }
}
