<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use App\Entity\Interface\TimeStampableInterface;
use App\State\Follow\FollowProcessor;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/users/{id}/follow',
            deserialize: false,
            name: 'follow',
            processor: FollowProcessor::class
        ),
        new Delete(
            uriTemplate: '/users/{id}/unfollow',
            read: false,
            name: 'unfollow',
            processor: FollowProcessor::class
        ),
    ],
)]
#[ORM\Entity]
#[ORM\Table(name: 'follow')]
class Follow implements TimeStampableInterface
{
    use Trait\TimeStampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'followerRelations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $follower = null;

    #[ORM\ManyToOne(inversedBy: 'followerRelations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $followed = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFollower(): ?User
    {
        return $this->follower;
    }

    public function setFollower(?User $follower): self
    {
        $this->follower = $follower;

        return $this;
    }

    public function getFollowed(): ?User
    {
        return $this->followed;
    }

    public function setFollowed(?User $followed): self
    {
        $this->followed = $followed;

        return $this;
    }
}
