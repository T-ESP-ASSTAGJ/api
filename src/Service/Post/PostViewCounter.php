<?php

declare(strict_types=1);

namespace App\Service\Post;

use App\Constants\RedisKeys;
use App\Entity\Post;

/**
 * Suit le comptage des vues de publications dans Redis avec un anti-rebond par utilisateur pour éviter les doublons.
 *
 * Une vue n'est comptabilisée qu'une seule fois par utilisateur dans la fenêtre TTL d'anti-rebond définie dans {@see RedisKeys::DEBOUNCE_TTL}.
 * Lorsque la clé Redis d'une publication n'existe pas encore, le compteur actuel en BDD est utilisé comme valeur initiale.
 * Les compteurs sont écrits en base de données de manière asynchrone par {@see PostViewsPersistenceService}.
 */
class PostViewCounter
{
    private \Redis $redis;

    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * Enregistre une vue pour la publication et l'utilisateur donnés, sous réserve du TTL d'anti-rebond.
     *
     * Utilise NX (set-if-not-exists) sur la clé d'anti-rebond afin que chaque utilisateur soit compté au plus une fois par fenêtre TTL.
     * Initialise le compteur Redis à partir de la valeur en BDD lorsque la clé n'existe pas encore.
     */
    public function increment(Post $post, int $userId): void
    {
        $postId = $post->getId();
        $debounceKey = RedisKeys::POST_VIEW_DEBOUNCE_PREFIX.$postId.':'.$userId;
        $viewsKey = RedisKeys::POST_VIEWS_PREFIX.$postId;

        $isNewView = $this->redis->set($debounceKey, '1', ['nx', 'ex' => RedisKeys::DEBOUNCE_TTL]);

        if ($isNewView) {
            if (!$this->redis->exists($viewsKey)) {
                $this->redis->set($viewsKey, (string) $post->getViewsCount());
            }

            $this->redis->incr($viewsKey);
        }
    }

    /** Retourne le compteur de vues actuel depuis Redis, en l'initialisant depuis la BDD lorsque la clé est absente. */
    public function getViews(Post $post): int
    {
        $postId = $post->getId();
        $viewsKey = RedisKeys::POST_VIEWS_PREFIX.$postId;

        $val = $this->redis->get($viewsKey);

        if (!$val) {
            $dbViews = $post->getViewsCount();
            $this->redis->set($viewsKey, (string) $dbViews);

            return $dbViews;
        }

        return (int) $val;
    }
}
