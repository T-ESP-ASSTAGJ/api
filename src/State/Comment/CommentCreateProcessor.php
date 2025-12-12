<?php

declare(strict_types=1);

namespace App\State\Comment;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\Comment\CommentCreateInput;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements ProcessorInterface<CommentCreateInput, Comment>
 */
final readonly class CommentCreateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
        private Security $security,
    ) {
    }

    /**
     * @param CommentCreateInput   $data
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return Comment
     */
    public function process(mixed $data, ?Operation $operation = null, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof CommentCreateInput) {
            $postId = $uriVariables['postId'] ?? null;

            if (!$postId) {
                throw new NotFoundHttpException('Post ID not found');
            }

            $post = $this->em->getRepository(Post::class)->find($postId);

            if (!$post) {
                throw new NotFoundHttpException('Post not found');
            }

            $user = $this->security->getUser();

            if (!$user instanceof User) {
                throw new \RuntimeException('User not authenticated');
            }

            $comment = new Comment();
            $comment->setPost($post);
            $comment->setUser($user);
            $comment->setContent($data->content);

            $violations = $this->validator->validate($comment);
            if ($violations->count() > 0) {
                throw new ValidationException($violations);
            }

            $this->em->persist($comment);
            $this->em->flush();

            return $comment;
        }

        return $data;
    }
}