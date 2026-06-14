<?php
/**
 * Boot order: services listed here are resolved from the container and have
 * their registerHooks() called during Plugin::boot(). Each must implement
 * Tabby\Contract\HasHooks. Admin-only services are absent outside wp-admin and
 * skipped gracefully (Plugin::boot() checks the container first).
 *
 * @package Tabby
 *
 * @return array<class-string>
 */

declare(strict_types=1);

use Tabby\Admin\Settings;
use Tabby\Service\TabsRenderer;

defined('ABSPATH') || exit;

return [
    TabsRenderer::class,
    ...(is_admin() ? [Settings::class] : []),
];
