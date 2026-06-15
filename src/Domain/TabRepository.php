<?php

declare(strict_types=1);

namespace Tabby\Domain;

defined('ABSPATH') || exit;

/**
 * Single source of truth for tab data.
 *
 * Global tabs live in the `tabby_settings` option and are read back through
 * {@see Tab} value objects, so callers never touch raw arrays and all content is
 * consistently sanitised.
 */
final class TabRepository
{
    public const OPTION = 'tabby_settings';

    /**
     * Whether Tabby is globally enabled.
     */
    public function isEnabled(): bool
    {
        return ! empty($this->settings()['enabled']);
    }

    /**
     * All configured global tabs (enabled and disabled), in stored order.
     *
     * @return array<int, Tab>
     */
    public function globalTabs(): array
    {
        $rows = $this->settings()['global_tabs'] ?? [];
        if (! is_array($rows)) {
            return [];
        }

        $tabs = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $tab = Tab::fromArray($row);
            if (null !== $tab) {
                $tabs[] = $tab;
            }
        }

        return $tabs;
    }

    /**
     * The full, ordered list of enabled tabs to render on a product page.
     *
     * The base plugin contributes its enabled global tabs; add-ons may add,
     * remove or reorder tabs through the `tabby/resolved_tabs` filter (e.g. to
     * scope global tabs to specific product categories). The current product is
     * passed so filters can make product-aware decisions.
     *
     * @param \WC_Product|null $product The product being rendered, when known.
     * @return array<int, Tab>
     */
    public function resolveTabs(?\WC_Product $product = null): array
    {
        if (! $this->isEnabled()) {
            return [];
        }

        $resolved = [];
        foreach ($this->globalTabs() as $tab) {
            if ($tab->enabled) {
                $resolved[] = $tab;
            }
        }

        /**
         * Filters the tabs resolved for the current product page.
         *
         * @param array<int, Tab>  $resolved Enabled tabs, in render order.
         * @param \WC_Product|null $product  The product being rendered, if known.
         */
        $filtered = apply_filters('tabby/resolved_tabs', $resolved, $product);

        if (! is_array($filtered)) {
            return $resolved;
        }

        // Defend the value-object contract: drop anything an add-on slipped in
        // that is not a Tab, so the renderer never sees a foreign shape.
        return array_values(array_filter(
            $filtered,
            static fn ($tab): bool => $tab instanceof Tab,
        ));
    }

    /**
     * Stored settings merged over packaged defaults.
     *
     * @return array<string, mixed>
     */
    public function settings(): array
    {
        $stored = get_option(self::OPTION, []);
        if (! is_array($stored)) {
            $stored = [];
        }

        /** @var array<string, mixed> $defaults */
        $defaults = require TABBY_DIR . 'config/defaults.php';

        return array_merge($defaults, $stored);
    }
}
