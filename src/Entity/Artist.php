<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post as ApiPost;
use App\DTO\Artist\ArtistCreateInput;
use App\DTO\Artist\ArtistGetOutput;
use App\DTO\Artist\ArtistUpdateInput;
use App\Entity\Interface\TimeStampableInterface;
use App\Processor\Artist\ArtistCreateProcessor;
use App\Processor\Artist\ArtistUpdateProcessor;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    shortName: 'Artist',
    operations: [
        new Get(output: ArtistGetOutput::class),
        new GetCollection(output: ArtistGetOutput::class),
        new ApiPost(
            input: ArtistCreateInput::class,
            output: ArtistGetOutput::class,
            processor: ArtistCreateProcessor::class
        ),
        new Patch(
            input: ArtistUpdateInput::class,
            output: ArtistGetOutput::class,
            processor: ArtistUpdateProcessor::class
        ),
        new Delete(output: false),
    ]
)]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'artist')]
class Artist implements TimeStampableInterface
{
    use Trait\TimeStampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    private string $name;

    /**
     * @var array<string, mixed>
     */
    #[ORM\Column(name: 'metadata', type: 'json')]
    private array $metadata = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function setMetadata(array $metadata): static
    {
        $this->metadata = $metadata;

        return $this;
    }
}