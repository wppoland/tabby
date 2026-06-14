<?php

declare(strict_types=1);

namespace Tabby\Admin;

use Tabby\Contract\HasHooks;
use Tabby\Domain\TabRepository;

defined('ABSPATH') || exit;

/**
 * Tabby settings screen, registered as a WooCommerce submenu.
 *
 * Manages the reusable global tabs (title + safe HTML content + enabled) stored
 * in the `tabby_settings` option. All output escaped; all input sanitised on
 * save; the save capability is aligned to manage_woocommerce.
 */
final class Settings implements HasHooks
{
    private const OPTION = 'tabby_settings';
    private const PAGE   = 'tabby-settings';

    public function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'addMenuPage']);
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function enqueueAssets(string $hookSuffix): void
    {
        if ('woocommerce_page_' . self::PAGE !== $hookSuffix) {
            return;
        }

        wp_enqueue_style(
            'tabby-admin',
            \Tabby\Plugin::instance()->url('assets/css/admin.css'),
            [],
            \Tabby\VERSION,
        );

        wp_enqueue_script(
            'tabby-repeater',
            \Tabby\Plugin::instance()->url('assets/js/repeater.js'),
            [],
            \Tabby\VERSION,
            ['in_footer' => true, 'strategy' => 'defer'],
        );
    }

    public function addMenuPage(): void
    {
        add_submenu_page(
            'woocommerce',
            __('Tabby - Custom Product Tabs', 'tabby'),
            __('Tabby Tabs', 'tabby'),
            'manage_woocommerce',
            self::PAGE,
            [$this, 'renderPage'],
        );
    }

    public function registerSettings(): void
    {
        register_setting(
            self::PAGE,
            self::OPTION,
            [
                'type'              => 'array',
                'sanitize_callback' => [$this, 'sanitize'],
            ],
        );

        add_filter(
            'option_page_capability_' . self::PAGE,
            static fn (): string => 'manage_woocommerce',
        );
    }

    public function renderPage(): void
    {
        if (! current_user_can('manage_woocommerce')) {
            return;
        }

        $repo       = new TabRepository();
        $settings   = $repo->settings();
        $globalTabs = $repo->globalTabs();
        ?>
        <div class="wrap tabby-admin">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div class="tabby-admin__intro">
                <span class="tabby-admin__intro-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" focusable="false">
                        <path fill="currentColor" d="M3 5a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5zm2 4v8h14V9H5z"/>
                    </svg>
                </span>
                <div class="tabby-admin__intro-text">
                    <h2><?php esc_html_e('Reusable tabs for every product page', 'tabby'); ?></h2>
                    <p><?php esc_html_e('Define tabs once and they appear on all single product pages, after the native WooCommerce tabs. Basic HTML is allowed in tab content.', 'tabby'); ?></p>
                </div>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields(self::PAGE); ?>

                <div class="tabby-admin__section">
                    <h2><?php esc_html_e('General', 'tabby'); ?></h2>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row"><?php esc_html_e('Enable Tabby', 'tabby'); ?></th>
                                <td>
                                    <label for="tabby_enabled">
                                        <input
                                            type="checkbox"
                                            id="tabby_enabled"
                                            name="<?php echo esc_attr(self::OPTION); ?>[enabled]"
                                            value="1"
                                            <?php checked((bool) ($settings['enabled'] ?? false), true); ?>
                                        />
                                        <?php esc_html_e('Render custom tabs on single product pages.', 'tabby'); ?>
                                    </label>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="tabby-admin__section">
                    <h2><?php esc_html_e('Tabs', 'tabby'); ?></h2>
                    <p class="tabby-admin__section-intro"><?php esc_html_e('These tabs appear on every product, in the order listed here.', 'tabby'); ?></p>

                    <div class="tabby-repeater" data-tabby-repeater>
                        <div class="tabby-repeater__rows" data-tabby-rows>
                            <?php
                            if ([] === $globalTabs) {
                                $this->renderGlobalRow(0, '', '', true);
                            } else {
                                foreach (array_values($globalTabs) as $index => $tab) {
                                    $this->renderGlobalRow((int) $index, $tab->title, $tab->content, $tab->enabled);
                                }
                            }
                            ?>
                        </div>

                        <template data-tabby-template>
                            <?php $this->renderGlobalRow(0, '', '', true, true); ?>
                        </template>

                        <p>
                            <button type="button" class="button button-secondary" data-tabby-add>
                                <?php esc_html_e('Add tab', 'tabby'); ?>
                            </button>
                        </p>
                    </div>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render a single global-tab repeater row.
     */
    private function renderGlobalRow(int $index, string $title, string $content, bool $enabled, bool $isTemplate = false): void
    {
        $i = $isTemplate ? '__index__' : (string) $index;
        $base = self::OPTION . '[global_tabs][' . $i . ']';
        ?>
        <div class="tabby-repeater__row" data-tabby-row>
            <div class="tabby-repeater__head">
                <label class="tabby-repeater__field tabby-repeater__field--title">
                    <span class="tabby-repeater__label"><?php esc_html_e('Tab title', 'tabby'); ?></span>
                    <input
                        type="text"
                        name="<?php echo esc_attr($base . '[title]'); ?>"
                        value="<?php echo esc_attr($title); ?>"
                        class="widefat"
                        placeholder="<?php esc_attr_e('e.g. Shipping & Returns', 'tabby'); ?>"
                    />
                </label>
                <label class="tabby-repeater__toggle">
                    <input
                        type="checkbox"
                        name="<?php echo esc_attr($base . '[enabled]'); ?>"
                        value="1"
                        <?php checked($enabled, true); ?>
                    />
                    <?php esc_html_e('Enabled', 'tabby'); ?>
                </label>
                <button type="button" class="button-link tabby-repeater__remove" data-tabby-remove>
                    <span aria-hidden="true">&times;</span>
                    <span class="screen-reader-text"><?php esc_html_e('Remove this tab', 'tabby'); ?></span>
                </button>
            </div>
            <label class="tabby-repeater__field">
                <span class="tabby-repeater__label"><?php esc_html_e('Tab content', 'tabby'); ?></span>
                <textarea
                    name="<?php echo esc_attr($base . '[content]'); ?>"
                    rows="4"
                    class="widefat"
                    placeholder="<?php esc_attr_e('Basic HTML is allowed (links, lists, bold, etc.).', 'tabby'); ?>"
                ><?php echo esc_textarea($content); ?></textarea>
            </label>
        </div>
        <?php
    }

    /**
     * Sanitise submitted settings before save.
     *
     * @param mixed $raw
     * @return array<string, mixed>
     */
    public function sanitize(mixed $raw): array
    {
        if (! is_array($raw)) {
            $raw = [];
        }

        /** @var array<string, mixed> $defaults */
        $defaults = require TABBY_DIR . 'config/defaults.php';

        $globalTabs = [];
        if (isset($raw['global_tabs']) && is_array($raw['global_tabs'])) {
            $position = 0;
            foreach ($raw['global_tabs'] as $entry) {
                if (! is_array($entry)) {
                    continue;
                }
                $title = isset($entry['title']) ? sanitize_text_field((string) $entry['title']) : '';
                if ('' === $title) {
                    continue;
                }
                $globalTabs[] = [
                    'id'      => $this->uniqueSlug($title, $position, $globalTabs),
                    'title'   => $title,
                    'content' => isset($entry['content']) ? wp_kses_post((string) $entry['content']) : '',
                    'enabled' => ! empty($entry['enabled']),
                ];
                ++$position;
            }
        }

        return array_merge($defaults, [
            'enabled'     => ! empty($raw['enabled']),
            'global_tabs' => $globalTabs,
        ]);
    }

    /**
     * Build a slug for a global tab, ensuring it does not collide with earlier
     * tabs in the same save.
     *
     * @param array<int, array<string, mixed>> $existing
     */
    private function uniqueSlug(string $title, int $position, array $existing): string
    {
        $base = sanitize_key($title);
        if ('' === $base) {
            $base = 'tab_' . $position;
        }

        $used = [];
        foreach ($existing as $tab) {
            if (isset($tab['id'])) {
                $used[] = (string) $tab['id'];
            }
        }

        $slug = $base;
        $n    = 1;
        while (in_array($slug, $used, true)) {
            $slug = $base . '_' . $n;
            ++$n;
        }

        return $slug;
    }
}
