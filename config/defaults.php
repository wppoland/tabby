<?php
/**
 * Default settings, stored under the `tabby_settings` option.
 *
 * `global_tabs` is the list of reusable tabs the merchant defines on the Tabby
 * settings screen; each renders on every single product page.
 *
 * @package Tabby
 *
 * @return array<string, mixed>
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

return [
    'enabled' => true,

    /*
     * Reusable global tabs. Each entry:
     *   id      => stable slug (a-z0-9_-), used as the tab key.
     *   title   => tab label (plain text).
     *   content => tab body (limited safe HTML, wp_kses_post).
     *   enabled => bool master toggle for this tab.
     *
     * @var array<int, array<string, mixed>>
     */
    'global_tabs' => [],
];
