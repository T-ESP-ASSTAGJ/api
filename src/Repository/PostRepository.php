<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Enum\LikeableTypeEnum;
use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    use SearchQueryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * @return Post[]
     */
    public function getPaginatedPosts(int $offset, int $limit): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param array<int> $followingUserIds
     *
     * @return Post[]
     */
    public function getFollowingPaginatedPosts(array $followingUserIds, int $offset, int $limit): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->where('p.user IN (:followingUserIds)')
            ->setParameter('followingUserIds', $followingUserIds)
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return Post[]
     */
    public function findByCaption(string $query, int $offset, int $limit): array
    {
        return $this->buildSearchQuery('caption', $query, $offset, $limit, 'createdAt', 'DESC')->getResult();
    }

    /**
     * @return Post[]
     */
    public function searchByTrackTitle(string $query, int $offset, int $limit): array
    {
        return $this->searchByTrackField('title', $query, $offset, $limit);
    }

    /**
     * @return Post[]
     */
    public function searchByArtistName(string $query, int $offset, int $limit): array
    {
        return $this->searchByTrackField('artistName', $query, $offset, $limit);
    }

    /**
     * Construit le profil d'interaction musicale de l'utilisateur à partir de ses likes et commentaires.
     *
     * Les tracks et artistes issus des commentaires sont maintenus séparément de ceux issus des likes
     * car un commentaire signale un intérêt plus fort — getScoredPaginatedPosts leur attribue
     * une pondération supérieure (+15/+8 vs +10/+5).
     *
     * @return array{likedTrackIds: int[], likedArtistNames: string[], commentedTrackIds: int[], commentedArtistNames: string[]}
     */
    public function getUserInteractionProfile(int $userId): array
    {
        $liked = $this->createQueryBuilder('p')
            ->select('DISTINCT IDENTITY(p.track) AS trackId, t.artistName')
            ->join('p.track', 't')
            ->where('p.id IN (SELECT l.entityId FROM App\Entity\Like l WHERE l.user = :uid AND l.entityClass = :cls)')
            ->setParameter('uid', $userId)
            ->setParameter('cls', LikeableTypeEnum::Post)
            ->getQuery()
            ->getArrayResult()
        ;

        $commented = $this->createQueryBuilder('p')
            ->select('DISTINCT IDENTITY(p.track) AS trackId, t.artistName')
            ->join('p.track', 't')
            ->join('p.comments', 'c')
            ->where('c.user = :uid')
            ->setParameter('uid', $userId)
            ->getQuery()
            ->getArrayResult()
        ;

        return [
            'likedTrackIds' => array_values(array_unique(array_filter(array_column($liked, 'trackId')))),
            'likedArtistNames' => array_values(array_unique(array_column($liked, 'artistName'))),
            'commentedTrackIds' => array_values(array_unique(array_filter(array_column($commented, 'trackId')))),
            'commentedArtistNames' => array_values(array_unique(array_column($commented, 'artistName'))),
        ];
    }

    /**
     * Retourne les posts paginés triés par score de pertinence décroissant, puis par date.
     *
     * Le score est calculé en base via des expressions CASE WHEN additives. Les signaux liés
     * au profil utilisateur (tracks, artistes, abonnements) sont ajoutés dynamiquement.
     *
     * Signaux et pondérations :
     *   +15 track commentée par l'utilisateur   +10 track likée
     *   +8  artiste commenté                     +5  artiste liké
     *   +4  auteur suivi                         +3  publication < 7 jours
     *   +3  likesCount > 50 / +2 > 10 / +1 > 0  (engagement)
     *
     * @param array{likedTrackIds: int[], likedArtistNames: string[], commentedTrackIds: int[], commentedArtistNames: string[]} $profile
     * @param int[] $followedUserIds
     *
     * @return Post[]
     */
    public function getScoredPaginatedPosts(array $profile, array $followedUserIds, int $offset, int $limit): array
    {
        $qb = $this->createQueryBuilder('p')->join('p.track', 't');

        $scoreParts = [
            'CASE WHEN p.createdAt >= :weekAgo THEN 3 ELSE 0 END',
            'CASE WHEN p.likesCount > 50 THEN 3 WHEN p.likesCount > 10 THEN 2 WHEN p.likesCount > 0 THEN 1 ELSE 0 END',
        ];

        $qb->setParameter('weekAgo', new \DateTimeImmutable('-7 days'));

        if (!empty($profile['commentedTrackIds'])) {
            $scoreParts[] = 'CASE WHEN IDENTITY(p.track) IN (:commentedTrackIds) THEN 15 ELSE 0 END';
            $qb->setParameter('commentedTrackIds', $profile['commentedTrackIds']);
        }

        if (!empty($profile['likedTrackIds'])) {
            $scoreParts[] = 'CASE WHEN IDENTITY(p.track) IN (:likedTrackIds) THEN 10 ELSE 0 END';
            $qb->setParameter('likedTrackIds', $profile['likedTrackIds']);
        }

        if (!empty($profile['commentedArtistNames'])) {
            $scoreParts[] = 'CASE WHEN t.artistName IN (:commentedArtistNames) THEN 8 ELSE 0 END';
            $qb->setParameter('commentedArtistNames', $profile['commentedArtistNames']);
        }

        if (!empty($profile['likedArtistNames'])) {
            $scoreParts[] = 'CASE WHEN t.artistName IN (:likedArtistNames) THEN 5 ELSE 0 END';
            $qb->setParameter('likedArtistNames', $profile['likedArtistNames']);
        }

        if (!empty($followedUserIds)) {
            $scoreParts[] = 'CASE WHEN p.user IN (:followedUserIds) THEN 4 ELSE 0 END';
            $qb->setParameter('followedUserIds', $followedUserIds);
        }

        $qb->select('p, ('.implode(' + ', $scoreParts).') AS HIDDEN score')
            ->orderBy('score', 'DESC')
            ->addOrderBy('p.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        return $qb->getQuery()->getResult();
    }

    public function updateViewsCount(int $postId, int $count): void
    {
        $this->createQueryBuilder('p')
            ->update()
            ->set('p.viewsCount', ':count')
            ->where('p.id = :id')
            ->setParameter('count', $count)
            ->setParameter('id', $postId)
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * @return Post[]
     */
    private function searchByTrackField(string $field, string $query, int $offset, int $limit): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.track', 't')
            ->where('t.'.$field.' LIKE :query')
            ->setParameter('query', '%'.$query.'%')
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
}
