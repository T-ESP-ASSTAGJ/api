<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\ApiResource\Report\ReportCreateInput;
use App\ApiResource\Report\ReportReasonOutput;
use App\Entity\Enum\ReportReasonEnum;
use App\Entity\Enum\ReportableTypeEnum;
use App\Entity\Interface\TimeStampableInterface;
use App\State\Report\ReportCreateProcessor;
use App\State\Report\ReportReasonsProvider;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    shortName: 'Report',
    operations: [
        new Post(
            uriTemplate: '/reports',
            status: 201,
            security: "is_granted('ROLE_USER')",
            input: ReportCreateInput::class,
            output: false,
            processor: ReportCreateProcessor::class,
        ),
        new GetCollection(
            uriTemplate: '/report-reasons',
            output: ReportReasonOutput::class,
            normalizationContext: ['groups' => [ReportReasonOutput::SERIALIZATION_GROUP_READ]],
            provider: ReportReasonsProvider::class,
        ),
    ]
)]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'report')]
#[ORM\UniqueConstraint(name: 'report_unique', columns: ['user_id', 'entity_id', 'entity_class'])]
class Report implements TimeStampableInterface
{
    use Trait\TimeStampableTrait;

    public const SERIALIZATION_GROUP_READ = 'report:read';

    #[Groups([self::SERIALIZATION_GROUP_READ])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[Groups([self::SERIALIZATION_GROUP_READ])]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    #[ORM\Column(name: 'entity_id', type: 'integer', nullable: false)]
    private int $entityId;

    #[ORM\Column(name: 'entity_class', type: 'string', length: 255, nullable: false, enumType: ReportableTypeEnum::class)]
    private ReportableTypeEnum $entityClass;

    #[Groups([self::SERIALIZATION_GROUP_READ])]
    #[ORM\Column(name: 'reason', type: 'string', length: 50, nullable: false, enumType: ReportReasonEnum::class)]
    private ReportReasonEnum $reason;

    #[Groups([self::SERIALIZATION_GROUP_READ])]
    #[ORM\Column(name: 'message', type: 'text', nullable: true)]
    private ?string $message = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User { return $this->user; }

    public function setUser(User $user): static { $this->user = $user; return $this; }

    public function getEntityId(): int { return $this->entityId; }

    public function setEntityId(int $entityId): static { $this->entityId = $entityId; return $this; }

    public function getEntityClass(): ReportableTypeEnum { return $this->entityClass; }

    public function setEntityClass(ReportableTypeEnum $entityClass): static { $this->entityClass = $entityClass; return $this; }

    public function getReason(): ReportReasonEnum { return $this->reason; }

    public function setReason(ReportReasonEnum $reason): static { $this->reason = $reason; return $this; }

    public function getMessage(): ?string { return $this->message; }

    public function setMessage(?string $message): static { $this->message = $message; return $this; }
}
