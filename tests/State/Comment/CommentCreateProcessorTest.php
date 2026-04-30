<?php

declare(strict_types=1);

namespace App\Tests\State\Comment;

use App\Entity\Comment;
use App\Factory\PostFactory;
use App\Factory\UserFactory;
use App\Message\CommentCreatedMessage;
use App\Tests\ApiTestCase;
use Symfony\Component\Security\Core\User\InMemoryUser;

class CommentCreateProcessorTest extends ApiTestCase
{
    public function testCreateCommentSuccessful(): void
    {
        $user = UserFactory::createOne(['roles' => ['ROLE_USER']]);
        $post = PostFactory::createOne();
        $client = $this->createAuthenticatedClient($user);

        $client->request('POST', '/api/posts/'.$post->getId().'/comments', [
            'json' => [
                'content' => 'This is a test comment',
            ],
        ]);

        self::assertResponseStatusCodeSame(201);
        self::assertJsonContains([
            'content' => 'This is a test comment',
            'user' => [
                'id' => $user->getId(),
            ],
        ]);

        $comment = static::getContainer()->get('doctrine')->getRepository(Comment::class)->findOneBy([
            'content' => 'This is a test comment',
            'post' => $post,
            'user' => $user,
        ]);

        self::assertNotNull($comment);
    }

    public function testCreateCommentPostNotFound(): void
    {
        $user = UserFactory::createOne(['roles' => ['ROLE_USER']]);
        $client = $this->createAuthenticatedClient($user);

        $client->request('POST', '/api/posts/9999/comments', [
            'json' => [
                'content' => 'This is a test comment',
            ],
        ]);

        self::assertResponseStatusCodeSame(404);
    }

    public function testCreateCommentUnauthenticated(): void
    {
        $post = PostFactory::createOne();

        static::createClient()->request('POST', '/api/posts/'.$post->getId().'/comments', [
            'json' => [
                'content' => 'This is a test comment',
            ],
        ]);

        self::assertResponseStatusCodeSame(401);
    }

    public function testCreateCommentInvalidContent(): void
    {
        $user = UserFactory::createOne(['roles' => ['ROLE_USER']]);
        $post = PostFactory::createOne();
        $client = $this->createAuthenticatedClient($user);

        $client->request('POST', '/api/posts/'.$post->getId().'/comments', [
            'json' => [
                'content' => '',
            ],
        ]);

        self::assertResponseStatusCodeSame(422);
    }

    public function testCreateCommentThrowsNotFoundWhenPostIdIsMissing(): void
    {
        $user = UserFactory::createOne(['roles' => ['ROLE_USER']]);
        $client = $this->createAuthenticatedClient($user);

        $client->request('POST', '/api/posts/toto/comments', [
            'json' => ['content' => 'Valid content'],
        ]);

        self::assertResponseStatusCodeSame(404);
    }
}
