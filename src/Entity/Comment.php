<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post as ApiPost;
use App\ApiResource\Comment\CommentCreateInput;
use App\Entity\Interface\TimeStampableInterface;
use App\State\Comment\CommentCreateProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    shortName: 'Comment',
    operations: [
        new Get(
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_DETAIL, User::SERIALIZATION_GROUP_READ]]
        ),
        new Delete(
            security: "is_granted('ROLE_USER') and object.getUser() == user",
            output: false
        ),
        new GetCollection(
            uriTemplate: '/posts/{postId}/comments',
            uriVariables: ['postId' => new Link(toProperty: 'post', fromClass: Post::class)],
            normalizationContext: [
                'groups' => [
                    self::SERIALIZATION_GROUP_READ,
                    User::SERIALIZATION_GROUP_READ,
                ]
            ]
        ),
        new ApiPost(
            uriTemplate: '/posts/{postId}/comments',
            uriVariables: ['postId' => new Link(toProperty: 'post', fromClass: Post::class)],
            normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_DETAIL, User::SERIALIZATION_GROUP_READ]],
            security: "is_granted('ROLE_USER')",
            input: CommentCreateInput::class,
            read: false,
            processor: CommentCreateProcessor::class
        ),
    ]
)]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'comment')]
class Comment implements TimeStampableInterface
{
    use Trait\TimeStampableTrait;

    public const SERIALIZATION_GROUP_READ = 'comment:read';
    public const SERIALIZATION_GROUP_DETAIL = 'comment:detail';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
    ])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(name: 'post_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Post $post;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
    ])]
    private User $user;

    #[ORM\Column(name: 'content', type: 'text')]
    #[Groups([
        self::SERIALIZATION_GROUP_READ,
        self::SERIALIZATION_GROUP_DETAIL,
    ])]
    private string $content;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPost(): Post
    {
        return $this->post;
    }

    public function setPost(Post $post): static
    {
        $this->post = $post;

        return $this;
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

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }
}
