<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interface\TimeStampableInterface;
use App\Entity\Trait\TimeStampableTrait;
use App\Repository\SpotifyTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SpotifyTokenRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'spotify_token')]
class SpotifyToken implements TimeStampableInterface
{
    use TimeStampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    #[ORM\Column(name: 'access_token', type: 'text')]
    private string $accessToken;

    #[ORM\Column(name: 'refresh_token', type: 'text', nullable: true)]
    private ?string $refreshToken = null;

    #[ORM\Column(name: 'expires_at', type: 'datetime')]
    private \DateTimeInterface $expiresAt;

    #[ORM\Column(name: 'spotify_user_id', type: 'string', length: 255)]
    private string $spotifyUserId;

    #[ORM\Column(name: 'scopes', type: 'json')]
    private array $scopes = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): static
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(?string $refreshToken): static
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function getExpiresAt(): \DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeInterface $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getSpotifyUserId(): string
    {
        return $this->spotifyUserId;
    }

    public function setSpotifyUserId(string $spotifyUserId): static
    {
        $this->spotifyUserId = $spotifyUserId;

        return $this;
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function setScopes(array $scopes): static
    {
        $this->scopes = $scopes;

        return $this;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt < new \DateTime();
    }
}
