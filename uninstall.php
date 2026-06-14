<?php
/**
 * Tabby uninstall routine. Removes the plugin's stored settings and version
 * marker.
 *
 * @package Tabby
 */

declare(strict_types=1);

defined('WP_UNINSTALL_PLUGIN') || exit;

delete_option('tabby_settings');
delete_option('tabby_db_version');
