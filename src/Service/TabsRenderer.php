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
 * Tabs are resolved per product from {@see TabRepository} (enabled global tabs
 * minus per-product hides, then per-product tabs). Priorities are spaced so the
 * configured ordering ('after' / 'before' the native tabs) is respected without
 * clobbering other plugins' tabs. Empty/disabled states render nothing.
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
        if (! $this->tabs->isEnabled()) {
            return $tabs;
        }

        $product = $this->currentProduct();
        if (null === $product) {
            return $tabs;
        }

        $resolved = $this->tabs->resolveForProduct($product->get_id());
        if ([] === $resolved) {
            return $tabs;
        }

        /**
         * Filter the resolved tabs before they are rendered. Add-ons and
         * themes can add, remove or reorder Tab objects here.
         *
         * @param array<int, Tab> $resolved Resolved Tab objects.
         * @param \WC_Product     $product  The current product.
         */
        $resolved = apply_filters('tabby/resolved_tabs', $resolved, $product);

        $this->current = [];

        // Base priority controls placement relative to native WC tabs (which use
        // 10/20/30). 'before' slots Tabby ahead of them; 'after' trails them.
        $priority = 'before' === $this->tabs->ordering() ? 5 : 100;
        $step     = 1;
        $seen     = [];

        foreach ($resolved as $tab) {
            if (! $tab instanceof Tab) {
                continue;
            }

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

            $priority += $step;
        }

        return $tabs;
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

        printf(
            '<div class="tabby-tab__content">%s</div>',
            wp_kses_post(wpautop($resolved->content)),
        );
    }

    /**
     * The product whose tabs are being rendered, or null when not on a product.
     */
    private function currentProduct(): ?\WC_Product
    {
        global $product;

        if ($product instanceof \WC_Product) {
            return $product;
        }

        if (function_exists('wc_get_product') && function_exists('get_the_ID')) {
            $id = get_the_ID();
            if (is_int($id) && $id > 0) {
                $maybe = wc_get_product($id);
                if ($maybe instanceof \WC_Product) {
                    return $maybe;
                }
            }
        }

        return null;
    }
}
