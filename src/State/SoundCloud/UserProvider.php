<?php

declare(strict_types=1);

namespace App\State\SoundCloud;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\SoundCloud\UserDTO;
use App\Entity\Token;
use App\Entity\User;
use App\Service\SoundCloud\SoundCloudService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @implements ProviderInterface<UserDTO>
 */
final readonly class UserProvider implements ProviderInterface
{
    public function __construct(
        private SoundCloudService $soundCloudService,
        private Security $security,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): UserDTO
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        if (null === $user) {
            throw new UnauthorizedHttpException('Bearer', 'Authentication required');
        }

        $token = $this->getSoundCloudToken($user);

        // Get : /soundcloud/users/{id}
        if (isset($uriVariables['id'])) {
            return $this->soundCloudService->getUser($token->getAccessToken(), (string) $uriVariables['id']);
        }

        throw new NotFoundHttpException('User not found');
    }

    private function getSoundCloudToken(User $user): Token
    {
        $token = $this->entityManager->getRepository(Token::class)->findOneBy([
            'user' => $user,
            'platform' => Token::PLATFORM_SOUNDCLOUD,
        ]);

        if (!$token || $token->isExpired()) {
            throw new UnauthorizedHttpException('Bearer', 'SoundCloud token not found or expired. Please authorize again.');
        }

        return $token;
    }
}