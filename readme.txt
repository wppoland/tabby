=== Tabby - Custom Product Tabs for WooCommerce ===
Contributors: motylanogha
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

Define your tabs once under **WooCommerce → Tabby Tabs** and they show up on every product. It suits content you'd otherwise paste into each product by hand: shipping and returns, size guides, care instructions, warranty notes.

Each tab is a title plus a content box that accepts the same limited HTML WordPress allows in posts (links, lists, bold, headings) via `wp_kses_post`. Your tabs render after the native WooCommerce tabs, and you can toggle each one on or off without deleting it.

The code lives at https://github.com/wppoland/tabby if you want to read it, report a bug or suggest a tab feature.

= Documentation and links =

* **Documentation** - https://plogins.com/tabby/docs/
* **Plugin page** - https://plogins.com/tabby/
* **Source code** - https://github.com/wppoland/tabby
* **Bug reports and feature requests** - https://github.com/wppoland/tabby/issues
* **Discussions and questions** - https://github.com/wppoland/tabby/discussions


= What it does =

* Adds your reusable tabs to every single product page, after Description, Additional information and Reviews.
* Stores tab content as `wp_kses_post`-sanitised HTML, both on save and again on output.
* Hooks the standard `woocommerce_product_tabs` filter at a late priority, so native and third-party tabs keep their place.
* Admin screen follows core WordPress styling and respects the editor's light/dark preference.
* A disabled tab, or one with no content, simply isn't rendered.

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

= Can I reuse the same tab on many products? =

Yes. Create reusable tabs once under WooCommerce → Tabby, then attach them per product.

= Is tab HTML safe? =

Yes. Content is sanitised with `wp_kses_post` on save and on output; scripts are stripped.

== Screenshots ==

1. The Tabby settings screen for managing reusable tabs.
2. Custom tabs rendered on the single product page.

== External Services ==

Tabby does not connect to any external services. It makes no remote API calls, loads no fonts, scripts or styles from a third party, and sends no data off your site. Your tab definitions are stored locally in a single WordPress option (`tabby_settings`), with a version marker in `tabby_db_version`, and both are removed on uninstall. The admin and front-end CSS and JavaScript it loads are bundled with the plugin and served from your own site.

== Changelog ==

= 0.1.0 =
* Initial release: reusable custom product tabs with safe HTML content, managed from a WooCommerce-submenu settings screen.
