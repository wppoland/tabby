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
     * The full, ordered list of enabled global tabs to render on a product page.
     *
     * @return array<int, Tab>
     */
    public function resolveTabs(): array
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

        return $resolved;
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
