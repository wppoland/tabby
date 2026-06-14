<?php

declare(strict_types=1);

namespace Tabby\Domain;

defined('ABSPATH') || exit;

/**
 * Immutable value object describing a single product tab.
 *
 * `content` holds limited, pre-sanitised HTML (wp_kses_post). `source` records
 * whether the tab came from the global settings or a per-product metabox, which
 * lets the renderer reason about provenance.
 */
final class Tab
{
    public const SOURCE_GLOBAL  = 'global';
    public const SOURCE_PRODUCT = 'product';

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
    public static function fromArray(array $raw, string $source = self::SOURCE_GLOBAL): ?self
    {
        $id    = isset($raw['id']) ? sanitize_key((string) $raw['id']) : '';
        $title = isset($raw['title']) ? sanitize_text_field((string) $raw['title']) : '';

        if ('' === $id || '' === $title) {
            return null;
        }

        $content = isset($raw['content']) ? wp_kses_post((string) $raw['content']) : '';
        $enabled = ! empty($raw['enabled']);

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
