<?php

declare(strict_types=1);

namespace App\State\Feed;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\FollowRepository;
use App\Repository\PostRepository;
use App\Service\isLikedEnricher;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Fournit le fil public paginé, trié par score de pertinence pour les utilisateurs authentifiés
 * ou par date décroissante pour les utilisateurs anonymes.
 *
 * Scoring (authentifié uniquement) — signaux cumulatifs :
 *   +15 track commentée  +10 track likée
 *   +8  artiste commenté  +5 artiste liké
 *   +4  auteur suivi      +3 publication < 7 jours
 *   +1/+2/+3 engagement (likesCount > 0 / > 10 / > 50)
 *
 * @see PostRepository::getUserInteractionProfile()
 * @see PostRepository::getScoredPaginatedPosts()
 *
 * @implements ProviderInterface<Post>
 */
final readonly class FeedPublicProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.pagination')]
        private Pagination $pagination,
        private PostRepository $postRepository,
        private isLikedEnricher $isLikedEnricher,
        private Security $security,
        private FollowRepository $followRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return array<Post>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $offset = $this->pagination->getOffset($operation, $context);
        $limit = $this->pagination->getLimit($operation, $context);

        $data = $this->resolveData($offset, $limit);

        $this->isLikedEnricher->enrich($data, Post::class);

        return $data;
    }

    /**
     * Retourne les posts chronologiques pour les anonymes, scorés pour les authentifiés.
     * Le scoring s'applique même sans historique d'interactions (fraîcheur + engagement).
     *
     * @return array<Post>
     */
    private function resolveData(int $offset, int $limit): array
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return $this->postRepository->getPaginatedPosts($offset, $limit);
        }

        $userId = $user->getId();
        $profile = $this->postRepository->getUserInteractionProfile($userId);
        $followedUsers = $this->followRepository->findFollowing($userId);
        $followedUserIds = array_values(array_filter(array_map(static fn ($u) => $u->id, $followedUsers)));

        return $this->postRepository->getScoredPaginatedPosts($profile, $followedUserIds, $offset, $limit);
    }
}
