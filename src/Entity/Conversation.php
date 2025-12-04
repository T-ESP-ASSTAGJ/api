<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post as ApiPost;
use App\ApiResource\Conversation\AddParticipantsInput;
use App\ApiResource\Conversation\ConversationCreateInput;
use App\ApiResource\Conversation\RemoveParticipantsInput;
use App\Entity\Interface\TimeStampableInterface;
use App\Repository\ConversationRepository;
use App\State\Conversation\AddParticipantsProcessor;
use App\State\Conversation\ConversationCreateProcessor;
use App\State\Conversation\ConversationLeaveProcessor;
use App\State\Conversation\ConversationListProvider;
use App\State\Conversation\RemoveParticipantsProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Conversation',
    operations: [
        new Get(
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_DETAIL], 'enable_max_depth' => true],
        ),
        new GetCollection(
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_READ], 'enable_max_depth' => true],
            provider: ConversationListProvider::class,
        ),
        new ApiPost(
            input: ConversationCreateInput::class,
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_DETAIL], 'enable_max_depth' => true],
            processor: ConversationCreateProcessor::class,
        ),
        new ApiPost(
            uriTemplate: '/conversations/{id}/leave',
            processor: ConversationLeaveProcessor::class,
            output: false,
        ),
        new ApiPost(
            uriTemplate: '/conversations/{id}/participants',
            input: AddParticipantsInput::class,
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_DETAIL], 'enable_max_depth' => true],
            processor: AddParticipantsProcessor::class,
        ),
        new Delete(
            uriTemplate: '/conversations/{id}/participants',
            input: RemoveParticipantsInput::class,
            processor: RemoveParticipantsProcessor::class,
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            output: false
        ),
    ],
)]
#[ORM\Entity(repositoryClass: ConversationRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'conversation')]
class Conversation implements TimeStampableInterface
{
    use Trait\TimeStampableTrait;

    public const SERIALIZATION_GROUP_READ = 'conversation:read';
    public const SERIALIZATION_GROUP_DETAIL = 'conversation:detail';
    public const SERIALIZATION_GROUP_WRITE = 'conversation:write';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
    ])]
    private ?int $id = null;

    #[ORM\Column(name: 'is_group', type: 'boolean', options: ['default' => false])]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        self::SERIALIZATION_GROUP_WRITE,
    ])]
    private bool $isGroup = false;

    #[ORM\Column(name: 'group_name', type: 'string', length: 255, nullable: true)]
    #[Assert\Length(min: 1, max: 255)]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
        self::SERIALIZATION_GROUP_WRITE,
    ])]
    private ?string $groupName = null;

    /** @var Collection<int, ConversationParticipant> */
    #[ORM\OneToMany(targetEntity: ConversationParticipant::class, mappedBy: 'conversation', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $participants;

    /** @var Collection<int, Message> */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'conversation', cascade: ['remove'], orphanRemoval: true)]
    private Collection $messages;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isGroup(): bool
    {
        return $this->isGroup;
    }

    public function setIsGroup(bool $isGroup): static
    {
        $this->isGroup = $isGroup;

        return $this;
    }

    public function getGroupName(): ?string
    {
        return $this->groupName;
    }

    public function setGroupName(?string $groupName): static
    {
        $this->groupName = $groupName;

        return $this;
    }

    /** @return Collection<int, ConversationParticipant> */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(ConversationParticipant $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
            $participant->setConversation($this);
        }

        return $this;
    }

    public function removeParticipant(ConversationParticipant $participant): static
    {
        if ($this->participants->removeElement($participant)) {
            if ($participant->getConversation() === $this) {
                $participant->setConversation(null);
            }
        }

        return $this;
    }

    /** @return Collection<int, Message> */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setConversation($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        $this->messages->removeElement($message);

        return $this;
    }

    /** @return array<int, array{user_id: int|null, username: string|null, profile_picture: string|null, role: string, joined_at: string, left_at: string|null}> */
    #[Groups([self::SERIALIZATION_GROUP_DETAIL])]
    public function getParticipantsList(): array
    {
        return $this->participants->map(fn (ConversationParticipant $participant) => [
            'user_id' => $participant->getUser()?->getId(),
            'username' => $participant->getUser()?->getUsername(),
            'profile_picture' => $participant->getUser()?->getProfilePicture(),
            'role' => $participant->getRole(),
            'joined_at' => $participant->getJoinedAt()->format('c'),
            'left_at' => $participant->getLeftAt()?->format('c'),
        ])->toArray();
    }

    #[Groups([self::SERIALIZATION_GROUP_READ, self::SERIALIZATION_GROUP_DETAIL])]
    public function getMemberCount(): int
    {
        return $this->participants->filter(
            fn (ConversationParticipant $p) => null === $p->getLeftAt()
        )->count();
    }

    /** @return Collection<int, ConversationParticipant> */
    public function getActiveParticipants(): Collection
    {
        return $this->participants->filter(
            fn (ConversationParticipant $p) => null === $p->getLeftAt()
        );
    }

    #[Groups([self::SERIALIZATION_GROUP_READ])]
    public function getType(): string
    {
        return $this->isGroup ? 'group' : 'private';
    }

    /** @return array{id: int, type: string, content: string|null, preview: string, author: array{id: int|null, username: string|null}, created_at: string}|null
     */
    #[Groups([self::SERIALIZATION_GROUP_READ])]
    public function getLastMessage(): ?array
    {
        $lastMessage = $this->messages
            ->filter(fn (Message $m) => null !== $m->getId())
            ->last();

        if (!$lastMessage instanceof Message) {
            return null;
        }

        $preview = $this->getMessagePreview($lastMessage);

        return [
            'id' => $lastMessage->getId(),
            'type' => $lastMessage->getType(),
            'content' => $lastMessage->getContent(),
            'preview' => $preview,
            'author' => [
                'id' => $lastMessage->getAuthor()->getId(),
                'username' => $lastMessage->getAuthor()->getUsername(),
            ],
            'created_at' => $lastMessage->getCreatedAt()->format('c'),
        ];
    }

    private function getMessagePreview(Message $message): string
    {
        if ($message->isMusicMessage()) {
            return 'Vous a partagé une musique';
        }

        return $message->getContent() ?? '';
    }

    public function getUnreadCountForUser(?User $user): int
    {
        if (null === $user) {
            return 0;
        }

        return $this->messages->filter(
            fn (Message $m) => !$m->isRead() && $m->getAuthor()->getId() !== $user->getId()
        )->count();
    }

    /**
     * Transient property to store unread count for serialization.
     */
    private ?int $unreadCount = null;

    #[Groups([self::SERIALIZATION_GROUP_READ])]
    public function getUnreadCount(): int
    {
        return $this->unreadCount ?? 0;
    }

    public function setUnreadCount(int $unreadCount): static
    {
        $this->unreadCount = $unreadCount;

        return $this;
    }

    /** @return array<int, array{id: int|null, username: string|null, profile_picture: string|null}>
     */
    #[Groups([self::SERIALIZATION_GROUP_READ])]
    public function getParticipantsInfo(): array
    {
        return $this->getActiveParticipants()->map(fn (ConversationParticipant $participant) => [
            'id' => $participant->getUser()?->getId(),
            'username' => $participant->getUser()?->getUsername(),
            'profile_picture' => $participant->getUser()?->getProfilePicture(),
        ])->getValues();
    }
}
