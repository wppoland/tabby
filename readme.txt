=== Tabby - Custom Product Tabs for WooCommerce ===
Contributors: wppoland
Tags: woocommerce, product tabs, custom tabs, product page, tabs
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 0.1.0
Requires Plugins: woocommerce
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add reusable custom tabs with your own content to every WooCommerce product page, alongside the native tabs — with safe HTML.

== Description ==

Tabby lets you add your own reusable tabs to the WooCommerce single product page, alongside the native Description, Additional information and Reviews tabs.

Define your tabs once under **WooCommerce → Tabby Tabs** and they appear on every product. Perfect for shared content like shipping & returns, size guides, care instructions or warranty information.

Each tab has a title and a content area that accepts safe, limited HTML (links, lists, bold, headings and more) via WordPress's `wp_kses_post`. Your custom tabs render after the native WooCommerce tabs.

= Highlights =

* Reusable tabs rendered on every product page.
* Safe HTML content (sanitised with `wp_kses_post`).
* Renders through the standard `woocommerce_product_tabs` filter with sensible priorities, so it plays nicely with themes and other plugins.
* Accessible, dark-mode-aware admin UI. No layout shift on the storefront.
* Graceful empty/disabled states — renders nothing rather than anything broken.

== Installation ==

1. Upload the plugin to `/wp-content/plugins/tabby`, or install via Plugins → Add New.
2. Activate it. WooCommerce must be active.
3. Go to **WooCommerce → Tabby Tabs** to add your tabs.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Yes. Tabby requires an active WooCommerce installation.

= What HTML is allowed in tab content? =

The same safe subset WordPress allows in post content (`wp_kses_post`): links, lists, headings, bold/italic, images, blockquotes and similar. Scripts and unsafe markup are stripped on save and on render.

= Where do the custom tabs appear? =

On the single product page tab list, after the native WooCommerce tabs (Description, Additional information, Reviews).

== Screenshots ==

1. The Tabby settings screen for managing reusable tabs.
2. Custom tabs rendered on the single product page.

== Changelog ==

= 0.1.0 =
* Initial release: reusable custom product tabs with safe HTML content, managed from a WooCommerce-submenu settings screen.
