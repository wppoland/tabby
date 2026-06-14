<?php
/**
 * Service wiring. Returns a closure that registers every service in the
 * container. Tabby is self-contained: the repository is the single source of
 * truth for tab data, the renderer injects tabs on the front end, and the
 * settings screen is only registered in wp-admin.
 *
 * @package Tabby
 */

declare(strict_types=1);

use Tabby\Admin\Settings;
use Tabby\Container;
use Tabby\Domain\TabRepository;
use Tabby\Migrator;
use Tabby\Service\TabsRenderer;

defined('ABSPATH') || exit;

return static function (Container $c): void {
    $c->singleton(Migrator::class, static fn (): Migrator => new Migrator());

    $c->singleton(TabRepository::class, static fn (): TabRepository => new TabRepository());

    $c->singleton(TabsRenderer::class, static fn (Container $c): TabsRenderer => new TabsRenderer(
        $c->get(TabRepository::class),
    ));

    if (is_admin()) {
        $c->singleton(Settings::class, static fn (): Settings => new Settings());
    }
};
