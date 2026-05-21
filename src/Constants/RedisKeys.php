<?php

declare(strict_types=1);

namespace App\Constants;

/**
 * Définit les constantes pour tous les schémas de clés Redis utilisés dans l'application.
 * Centralise la gestion des clés Redis pour une meilleure maintenabilité et découvrabilité.
 */
final readonly class RedisKeys
{
    public const DEBOUNCE_TTL = 3600;
    public const POST_VIEWS_PREFIX = 'post_views:';
    public const POST_VIEW_DEBOUNCE_PREFIX = 'post_view_debounce:';
}
