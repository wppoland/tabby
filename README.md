# Tabby - Custom Product Tabs for WooCommerce

Tabby lets you add your own tabs to WooCommerce single product pages, alongside the native
Description, Additional information and Reviews tabs — perfect for shipping details, size guides,
care instructions or warranty information.

## Features

- Reusable global tabs shown on every product, managed under WooCommerce → Tabby Tabs.
- Per-product tabs added from a box on the product editor.
- Hide specific global tabs on individual products.
- Safe HTML content, sanitised with `wp_kses_post`.
- Choose whether custom tabs appear before or after the native WooCommerce tabs.
- Accessible, dark-mode-aware admin UI that causes no layout shift on the storefront.

## Installation

1. Upload the plugin to `/wp-content/plugins/tabby`, or install it via Plugins → Add New.
2. Activate it. WooCommerce must be active.
3. Go to WooCommerce → Tabby Tabs to add global tabs, or open any product to add per-product tabs.

## Frequently Asked Questions

**Does it require WooCommerce?**
Yes. Tabby requires an active WooCommerce installation.

**What HTML is allowed in tab content?**
The same safe subset WordPress allows in post content (`wp_kses_post`): links, lists, headings,
bold/italic, images, blockquotes and similar. Scripts and unsafe markup are stripped.

Built by WPPoland — https://plogins.com

License: GPL-2.0-or-later
