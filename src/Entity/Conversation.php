<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\ApiResource\Conversation\ConversationGetOutput;
use App\Entity\Interface\TimeStampableInterface;
use App\Repository\ConversationRepository;
use App\State\Conversation\ConversationListProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    shortName: 'Conversation',
    operations: [
        new Get(
            output: ConversationGetOutput::class,
        ),
        new GetCollection(
            output: ConversationGetOutput::class,
            provider: ConversationListProvider::class
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

    /** @var Collection<int, User> */
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'conversation_participant')]
    #[ORM\JoinColumn(name: 'conversation_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private Collection $participants;

    /** @var Collection<int, Message> */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'conversation', cascade: ['persist'])]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /** @return Collection<int, User> */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
        }

        return $this;
    }

    public function removeParticipant(User $participant): static
    {
        $this->participants->removeElement($participant);

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
}
