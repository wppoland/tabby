<?php

declare(strict_types=1);

namespace Tabby\Domain;

defined('ABSPATH') || exit;

/**
 * Immutable value object describing a single product tab.
 *
 * `content` holds limited, pre-sanitised HTML (wp_kses_post).
 */
final class Tab
{
    /**
     * A reusable tab defined on the settings screen and shown on every product.
     */
    public const SOURCE_GLOBAL = 'global';

    /**
     * A tab attached to a single product. Reserved for add-ons that store
     * per-product tabs; the free plugin only produces global tabs.
     */
    public const SOURCE_PRODUCT = 'product';

    /**
     * @param string $source One of self::SOURCE_* describing where the tab
     *                       originates. Add-ons (e.g. category rules) branch on
     *                       this to decide whether a tab is subject to their
     *                       scoping rules.
     */
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $content,
        public readonly bool $enabled = true,
        public readonly string $source = self::SOURCE_GLOBAL,
    ) {
    }

    /**
     * Build a Tab from a loosely-typed array, sanitising every field. Returns
     * null when the row has no usable id or title.
     *
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): ?self
    {
        $id    = isset($raw['id']) ? sanitize_key((string) $raw['id']) : '';
        $title = isset($raw['title']) ? sanitize_text_field((string) $raw['title']) : '';

        if ('' === $id || '' === $title) {
            return null;
        }

        $content = isset($raw['content']) ? wp_kses_post((string) $raw['content']) : '';
        $enabled = ! empty($raw['enabled']);

        $source = isset($raw['source']) ? sanitize_key((string) $raw['source']) : self::SOURCE_GLOBAL;
        if (self::SOURCE_PRODUCT !== $source) {
            $source = self::SOURCE_GLOBAL;
        }

        return new self($id, $title, $content, $enabled, $source);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'      => $this->id,
            'title'   => $this->title,
            'content' => $this->content,
            'enabled' => $this->enabled,
            'source'  => $this->source,
        ];
    }
}
