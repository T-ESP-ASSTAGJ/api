<?php

declare(strict_types=1);

namespace App\State\SoundCloud;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\SoundCloud\TrackDTO;
use App\Entity\Token;
use App\Entity\User;
use App\Service\SoundCloud\SoundCloudService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @implements ProviderInterface<TrackDTO>
 */
final readonly class TrackProvider implements ProviderInterface
{
    public function __construct(
        private SoundCloudService $soundCloudService,
        private Security $security,
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     * @return TrackDTO|TrackDTO[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        if (null === $user) {
            throw new UnauthorizedHttpException('Bearer', 'Authentication required');
        }

        $token = $this->getSoundCloudToken($user);

        // GetCollection : /soundcloud/tracks/search?q=...
        if ($operation instanceof \ApiPlatform\Metadata\GetCollection) {
            $request = $this->requestStack->getMainRequest();
            $query = $request?->query->get('q');

            if (!$query) {
                throw new BadRequestHttpException('Query parameter "q" is required for search');
            }

            $limit = $request->query->getInt('limit', 20);

            return $this->soundCloudService->searchMusic($token->getAccessToken(), $query, $limit);
        }

        // Get : /soundcloud/tracks/{id}
        if (isset($uriVariables['id'])) {
            return $this->soundCloudService->getTrack($token->getAccessToken(), (string) $uriVariables['id']);
        }

        throw new NotFoundHttpException('Track not found');
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