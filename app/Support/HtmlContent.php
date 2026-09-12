<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class HtmlContent
{
    /**
     * @var list<string>
     */
    protected static array $allowedTags = [
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's',
        'ul', 'ol', 'li', 'a', 'img', 'blockquote', 'figure', 'figcaption', 'hr',
        'table', 'thead', 'tbody', 'tr', 'th', 'td', 'span', 'div', 'pre', 'code',
    ];

    /**
     * @var array<string, list<string>>
     */
    protected static array $allowedAttributes = [
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'class', 'style'],
        'h1' => ['id', 'class'],
        'h2' => ['id', 'class'],
        'h3' => ['id', 'class'],
        'h4' => ['id', 'class'],
        'h5' => ['id', 'class'],
        'h6' => ['id', 'class'],
        'p' => ['class'],
        'div' => ['class'],
        'span' => ['class'],
        'figure' => ['class'],
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan'],
    ];

    public static function clean(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $internal = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><div id="qf-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($internal);

        $root = $document->getElementById('qf-root');
        if (! $root instanceof DOMElement) {
            return '';
        }

        self::scrub($root);

        $clean = '';
        foreach ($root->childNodes as $child) {
            $clean .= $document->saveHTML($child);
        }

        return trim($clean);
    }

    protected static function scrub(DOMNode $node): void
    {
        $remove = [];

        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $tag = strtolower($child->tagName);
                if (! in_array($tag, self::$allowedTags, true)) {
                    $remove[] = $child;

                    continue;
                }

                self::scrubAttributes($child, $tag);
                self::scrub($child);
            }
        }

        foreach ($remove as $child) {
            $child->parentNode?->removeChild($child);
        }
    }

    protected static function scrubAttributes(DOMElement $element, string $tag): void
    {
        $allowed = self::$allowedAttributes[$tag] ?? [];
        $names = [];
        foreach ($element->attributes ?? [] as $attribute) {
            $names[] = $attribute->name;
        }

        foreach ($names as $name) {
            $value = $element->getAttribute($name);
            if (! in_array($name, $allowed, true) || self::isDangerous($name, $value)) {
                $element->removeAttribute($name);

                continue;
            }

            if ($name === 'style') {
                $element->setAttribute('style', self::safeStyle($value));
            }

            if ($tag === 'a' && $name === 'target' && $value === '_blank') {
                $element->setAttribute('rel', 'noopener noreferrer');
            }
        }

        if ($tag === 'img' && ! $element->hasAttribute('alt')) {
            $element->setAttribute('alt', '');
        }
    }

    protected static function isDangerous(string $name, string $value): bool
    {
        $value = trim($value);
        if ($value === '') {
            return $name !== 'alt';
        }

        if (in_array($name, ['href', 'src'], true)) {
            if (str_starts_with($value, '/') && ! str_starts_with($value, '//')) {
                return false;
            }

            return (bool) preg_match('/^\s*(javascript|data|vbscript):/i', $value)
                || ! preg_match('/^(https?:\/\/|mailto:)/i', $value);
        }

        return (bool) preg_match('/^\s*javascript:/i', $value);
    }

    protected static function safeStyle(string $style): string
    {
        $safe = [];
        foreach (explode(';', $style) as $rule) {
            [$property, $value] = array_pad(explode(':', $rule, 2), 2, null);
            $property = strtolower(trim((string) $property));
            $value = trim((string) $value);
            if ($value === '' || ! in_array($property, ['width', 'height', 'max-width'], true)) {
                continue;
            }
            if (preg_match('/^(auto|\d+(\.\d+)?(px|%))$/i', $value) !== 1) {
                continue;
            }
            $safe[] = $property.': '.$value;
        }

        return implode('; ', $safe);
    }
}
