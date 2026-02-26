<?php

declare(strict_types=1);

namespace App\Constants;

/**
 * Defines constants for all Redis key patterns used in the application.
 * This centralizes Redis key management for improved maintainability and discoverability.
 */
final readonly class RedisKeys
{
    public const DEBOUNCE_TTL = 3600;
    public const POST_VIEWS_PREFIX = 'post_views:';
    public const POST_VIEW_DEBOUNCE_PREFIX = 'post_view_debounce:';
}
