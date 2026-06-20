<?php

declare(strict_types=1);

namespace Tabby\Service;

use Tabby\Contract\HasHooks;
use Tabby\Domain\Tab;
use Tabby\Domain\TabRepository;

defined('ABSPATH') || exit;

/**
 * Injects Tabby's custom tabs into the single product page via the
 * `woocommerce_product_tabs` filter.
 *
 * Tabs are the enabled global tabs from {@see TabRepository}, appended after the
 * native WooCommerce tabs. Empty/disabled states render nothing.
 */
final class TabsRenderer implements HasHooks
{
    /**
     * Tabs resolved for the current product, keyed by their unique render key.
     *
     * @var array<string, Tab>
     */
    private array $current = [];

    public function __construct(private readonly TabRepository $tabs)
    {
    }

    public function registerHooks(): void
    {
        add_filter('woocommerce_product_tabs', [$this, 'addTabs'], 98);
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    /**
     * Enqueue the small panel stylesheet on single product pages only.
     */
    public function enqueueAssets(): void
    {
        if (! $this->tabs->isEnabled()) {
            return;
        }

        if (! function_exists('is_product') || ! is_product()) {
            return;
        }

        wp_enqueue_style(
            'tabby-tabs',
            \Tabby\Plugin::instance()->url('assets/css/tabs.css'),
            [],
            \Tabby\VERSION,
        );
    }

    /**
     * @param array<string, array<string, mixed>> $tabs
     * @return array<string, array<string, mixed>>
     */
    public function addTabs(array $tabs): array
    {
        $resolved = $this->tabs->resolveTabs($this->currentProduct());
        if ([] === $resolved) {
            return $tabs;
        }

        $this->current = [];

        // Native WooCommerce tabs use priorities 10/20/30, so 100+ trails them.
        $priority = 100;
        $seen     = [];

        foreach ($resolved as $tab) {
            // Guarantee a unique array key even if two tabs share an id.
            $key = 'tabby_' . $tab->id;
            $n   = 1;
            while (isset($seen[$key])) {
                $key = 'tabby_' . $tab->id . '_' . $n;
                ++$n;
            }
            $seen[$key]          = true;
            $this->current[$key] = $tab;

            $tabs[$key] = [
                'title'    => $tab->title,
                'priority' => $priority,
                'callback' => [$this, 'renderPanel'],
            ];

            ++$priority;
        }

        return $tabs;
    }

    /**
     * The product currently being rendered, when WooCommerce has set it.
     *
     * `woocommerce_product_tabs` fires inside the single-product template, where
     * the global `$product` is the product on screen.
     */
    private function currentProduct(): ?\WC_Product
    {
        $product = $GLOBALS['product'] ?? null;

        return $product instanceof \WC_Product ? $product : null;
    }

    /**
     * Render a single tab panel. WooCommerce calls this with the tab key and the
     * tab definition array.
     *
     * @param array<string, mixed> $tab
     */
    public function renderPanel(string $key, array $tab): void
    {
        $resolved = $this->current[$key] ?? null;
        if (! $resolved instanceof Tab) {
            return;
        }

        if ('' !== $resolved->title) {
            printf(
                '<h2 class="tabby-tab__title">%s</h2>',
                esc_html($resolved->title),
            );
        }

        if ('' === trim($resolved->content)) {
            return;
        }

        $html = $this->formatPanelHtml($resolved->content, $resolved, $this->currentProduct());

        if ('' === trim(wp_strip_all_tags($html))) {
            return;
        }

        printf(
            '<div class="tabby-tab__content">%s</div>',
            $html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitised in formatPanelHtml or via the_content when PRO is active.
        );
    }

    /**
     * Turn stored tab body markup into storefront HTML.
     *
     * @param string           $content Raw tab body markup.
     * @param Tab              $tab     The tab being rendered.
     * @param \WC_Product|null $product The product being viewed.
     */
    private function formatPanelHtml(string $content, Tab $tab, ?\WC_Product $product): string
    {
        // Filter: tabby/use_rich_tab_content — premium add-ons enable shortcode/block processing.
        if ((bool) apply_filters('tabby/use_rich_tab_content', false, $tab, $product)) {
            // Filter: tabby/tab_panel_html — rich tab panel HTML after the_content.
            return (string) apply_filters('tabby/tab_panel_html', apply_filters('the_content', $content), $tab, $product);
        }

        $html = wp_kses_post(wpautop($content));

        // Filter: tabby/tab_panel_html — plain sanitised tab panel HTML.
        return (string) apply_filters('tabby/tab_panel_html', $html, $tab, $product);
    }
}
