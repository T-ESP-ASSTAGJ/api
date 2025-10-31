<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use App\ApiResource\Follow\FollowOutput;
use App\Entity\Interface\TimeStampableInterface;
use App\State\Follow\FollowProcessor;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/users/{id}/follow',
            defaults: ['_api_receive' => false],
            input: false,
            name: 'follow',
            processor: FollowProcessor::class
        ),
        new Delete(
            uriTemplate: '/users/{id}/unfollow',
            output: FollowOutput::class,
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

    #[ORM\ManyToOne(inversedBy: 'following')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $follower = null;  // The user who follows

    #[ORM\ManyToOne(inversedBy: 'followers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $followedUser = null;  // The user being followed

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

    public function getFollowedUser(): ?User
    {
        return $this->followedUser;
    }

    public function setFollowedUser(?User $followedUser): self
    {
        $this->followedUser = $followedUser;

        return $this;
    }
}
