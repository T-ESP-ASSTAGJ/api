<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post as ApiPost;
use App\ApiResource\Conversation\ConversationCreateInput;
use App\ApiResource\Conversation\ConversationDetailOutput;
use App\ApiResource\Conversation\ConversationGetOutput;
use App\Entity\Interface\TimeStampableInterface;
use App\Repository\ConversationRepository;
use App\State\Conversation\ConversationCreateProcessor;
use App\State\Conversation\ConversationGetProvider;
use App\State\Conversation\ConversationLeaveProcessor;
use App\State\Conversation\ConversationListProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    shortName: 'Conversation',
    operations: [
        new Get(
            output: ConversationDetailOutput::class,
            provider: ConversationGetProvider::class
        ),
        new GetCollection(
            output: ConversationGetOutput::class,
            provider: ConversationListProvider::class
        ),
        new ApiPost(
            uriTemplate: '/conversations',
            input: ConversationCreateInput::class,
            output: ConversationDetailOutput::class,
            processor: ConversationCreateProcessor::class
        ),
        new ApiPost(
            uriTemplate: '/conversations/{id}/leave',
            input: false,
            output: false,
            processor: ConversationLeaveProcessor::class
        ),
    ]
)]
#[ORM\Entity(repositoryClass: ConversationRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'conversation')]
class Conversation implements TimeStampableInterface
{
    use Trait\TimeStampableTrait;

    public const TYPE_PRIVATE = 'private';
    public const TYPE_GROUP = 'group';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'type', type: 'string', length: 20)]
    private string $type = self::TYPE_PRIVATE;

    #[ORM\Column(name: 'group_name', type: 'string', length: 255, nullable: true)]
    private ?string $groupName = null;

    /** @var Collection<int, ConversationParticipant> */
    #[ORM\OneToMany(targetEntity: ConversationParticipant::class, mappedBy: 'conversation', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $conversationParticipants;

    /** @var Collection<int, Message> */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'conversation', cascade: ['persist'])]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $messages;

    public function __construct()
    {
        $this->conversationParticipants = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
    public function getConversationParticipants(): Collection
    {
        return $this->conversationParticipants;
    }

    public function addConversationParticipant(ConversationParticipant $conversationParticipant): static
    {
        if (!$this->conversationParticipants->contains($conversationParticipant)) {
            $this->conversationParticipants->add($conversationParticipant);
            $conversationParticipant->setConversation($this);
        }

        return $this;
    }

    public function removeConversationParticipant(ConversationParticipant $conversationParticipant): static
    {
        $this->conversationParticipants->removeElement($conversationParticipant);

        return $this;
    }

    /**
     * Get active participants
     *
     * @return array<User>
     */
    public function getActiveParticipants(): array
    {
        return $this->conversationParticipants
            ->filter(fn (ConversationParticipant $cp) => $cp->isActive())
            ->map(fn (ConversationParticipant $cp) => $cp->getUser())
            ->toArray();
    }

    /**
     * Check if a user is an active participant.
     */
    public function isUserParticipant(User $user): bool
    {
        foreach ($this->conversationParticipants as $cp) {
            if ($cp->getUser()->getId() === $user->getId() && $cp->isActive()) {
                return true;
            }
        }

        return false;
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

    public function getLastMessage(): ?Message
    {
        $messages = $this->messages->toArray();

        return !empty($messages) ? $messages[0] : null;
    }

    /**
     * Count unread messages for a specific user.
     */
    public function getUnreadCountForUser(User $user): int
    {
        // TODO: Implement unread message tracking
        // For now, return 0
        return 0;
    }

    /**
     * Check if conversation should be deleted (no active participants).
     */
    public function shouldBeDeleted(): bool
    {
        foreach ($this->conversationParticipants as $cp) {
            if ($cp->isActive()) {
                return false;
            }
        }

        return true;
    }
}
