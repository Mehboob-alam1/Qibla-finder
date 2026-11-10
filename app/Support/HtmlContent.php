<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Support\Str;

class HtmlContent
{
    /**
     * Tags kept as-is after import/clean.
     *
     * @var list<string>
     */
    protected static array $allowedTags = [
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'p', 'br', 'hr', 'strong', 'b', 'em', 'i', 'u', 's', 'mark', 'small', 'sub', 'sup', 'abbr', 'time',
        'ul', 'ol', 'li', 'dl', 'dt', 'dd',
        'a', 'img', 'picture', 'source', 'figure', 'figcaption',
        'blockquote', 'pre', 'code',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'caption', 'colgroup', 'col',
        'span', 'div', 'section', 'article', 'header', 'footer', 'main', 'nav', 'aside', 'address',
        'details', 'summary', 'wbr',
        'video', 'audio', 'iframe',
    ];

    /**
     * Tags that must be removed entirely (including children).
     *
     * @var list<string>
     */
    protected static array $stripTags = [
        'script', 'style', 'noscript', 'template', 'object', 'embed', 'applet',
        'link', 'meta', 'base', 'form', 'input', 'button', 'select', 'textarea', 'option',
        'svg', 'math', 'canvas',
    ];

    /**
     * @var array<string, list<string>>
     */
    protected static array $allowedAttributes = [
        '*' => ['id', 'class', 'title', 'lang', 'dir', 'role', 'aria-label', 'aria-hidden', 'aria-describedby', 'aria-labelledby'],
        'a' => ['href', 'target', 'rel', 'download'],
        'img' => ['src', 'alt', 'width', 'height', 'loading', 'decoding', 'srcset', 'sizes', 'style'],
        'source' => ['src', 'srcset', 'sizes', 'type', 'media'],
        'video' => ['src', 'controls', 'poster', 'width', 'height', 'preload', 'playsinline', 'muted', 'loop', 'autoplay'],
        'audio' => ['src', 'controls', 'preload', 'loop', 'muted', 'autoplay'],
        'iframe' => ['src', 'width', 'height', 'title', 'loading', 'allow', 'allowfullscreen', 'referrerpolicy', 'frameborder'],
        'td' => ['colspan', 'rowspan', 'scope'],
        'th' => ['colspan', 'rowspan', 'scope'],
        'col' => ['span'],
        'colgroup' => ['span'],
        'time' => ['datetime'],
        'details' => ['open'],
    ];

    /**
     * Parse a full HTML document or fragment into CMS fields.
     *
     * @return array{title: string, meta_title: string, meta_description: string, slug: string, content: string}
     */
    public static function importDocument(string $html, ?string $filename = null): array
    {
        $html = trim($html);
        if ($html === '') {
            return [
                'title' => '',
                'meta_title' => '',
                'meta_description' => '',
                'slug' => '',
                'content' => '',
            ];
        }

        $document = self::load($html);
        $title = self::textContent($document->getElementsByTagName('title')->item(0));
        $metaDescription = self::metaContent($document, 'description');
        $metaTitle = self::metaContent($document, 'og:title') ?: $title;

        $body = $document->getElementsByTagName('body')->item(0);
        if ($body instanceof DOMElement) {
            $fragment = self::innerHtml($document, $body);
        } else {
            // Fragment / partial HTML — use the whole payload.
            $fragment = $html;
        }

        $content = self::clean($fragment);

        if ($title === '') {
            $title = self::firstHeading($content) ?: self::titleFromFilename($filename) ?: 'Imported page';
        }

        $slug = Str::slug($title);
        // Never publish as /index or /home just because the file was named index.html.
        if ($slug === '' || in_array($slug, ['index', 'home', 'default', 'untitled'], true)) {
            $fromFile = self::slugFromFilename($filename);
            $slug = $fromFile !== '' && ! in_array($fromFile, ['index', 'home', 'default'], true)
                ? $fromFile
                : Str::slug($title.'-page');
        }

        return [
            'title' => Str::limit($title, 180, ''),
            'meta_title' => Str::limit($metaTitle !== '' ? $metaTitle : $title, 180, ''),
            'meta_description' => Str::limit($metaDescription !== '' ? $metaDescription : Str::limit(strip_tags($content), 300, ''), 300, ''),
            'slug' => $slug,
            'content' => $content,
        ];
    }

    protected static function titleFromFilename(?string $filename): string
    {
        $slug = self::slugFromFilename($filename);
        if ($slug === '' || in_array($slug, ['index', 'home', 'default'], true)) {
            return '';
        }

        return Str::title(str_replace('-', ' ', $slug));
    }

    protected static function slugFromFilename(?string $filename): string
    {
        if ($filename === null || $filename === '') {
            return '';
        }

        $base = pathinfo($filename, PATHINFO_FILENAME);

        return Str::slug((string) $base);
    }

    public static function clean(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        // If a full document was pasted into the editor, keep only the body.
        if (preg_match('/<\s*(html|body|head)\b/i', $html) === 1) {
            $document = self::load($html);
            $body = $document->getElementsByTagName('body')->item(0);
            if ($body instanceof DOMElement) {
                $html = self::innerHtml($document, $body);
            }
        }

        $document = self::load('<div id="qf-root">'.$html.'</div>');
        $root = $document->getElementById('qf-root');
        if (! $root instanceof DOMElement) {
            return '';
        }

        self::scrub($root);

        return trim(self::innerHtml($document, $root));
    }

    protected static function load(string $html): DOMDocument
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $internal = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($internal);

        return $document;
    }

    protected static function scrub(DOMNode $node): void
    {
        /** @var list<DOMNode> $queue */
        $queue = [];
        foreach ($node->childNodes as $child) {
            $queue[] = $child;
        }

        foreach ($queue as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::$stripTags, true)) {
                $child->parentNode?->removeChild($child);

                continue;
            }

            if (! in_array($tag, self::$allowedTags, true)) {
                // Unwrap unknown wrappers so nested safe content survives import.
                self::unwrap($child);

                continue;
            }

            self::scrubAttributes($child, $tag);
            self::scrub($child);
        }
    }

    protected static function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;
        if ($parent === null) {
            return;
        }

        self::scrub($element);

        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }

    protected static function scrubAttributes(DOMElement $element, string $tag): void
    {
        $allowed = array_values(array_unique(array_merge(
            self::$allowedAttributes['*'] ?? [],
            self::$allowedAttributes[$tag] ?? [],
            // Common layout attrs on structural tags.
            in_array($tag, ['div', 'section', 'article', 'header', 'footer', 'main', 'nav', 'aside', 'p', 'span', 'figure', 'ul', 'ol', 'li', 'table'], true)
                ? ['style']
                : [],
        )));

        $names = [];
        foreach ($element->attributes ?? [] as $attribute) {
            $names[] = $attribute->name;
        }

        foreach ($names as $name) {
            $lower = strtolower($name);
            $value = $element->getAttribute($name);

            if (str_starts_with($lower, 'data-') || str_starts_with($lower, 'aria-')) {
                if (self::isDangerous($lower, $value)) {
                    $element->removeAttribute($name);
                }

                continue;
            }

            if (! in_array($lower, $allowed, true) || self::isDangerous($lower, $value)) {
                $element->removeAttribute($name);

                continue;
            }

            if ($lower === 'style') {
                $safe = self::safeStyle($value);
                if ($safe === '') {
                    $element->removeAttribute('style');
                } else {
                    $element->setAttribute('style', $safe);
                }
            }

            if ($tag === 'a' && $lower === 'target' && $value === '_blank') {
                $element->setAttribute('rel', 'noopener noreferrer');
            }

            if ($tag === 'iframe' && $lower === 'src' && ! self::allowedIframeSrc($value)) {
                $element->parentNode?->removeChild($element);

                return;
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
            return ! in_array($name, ['alt', 'title', 'aria-label', 'aria-hidden'], true);
        }

        if (preg_match('/^\s*javascript:/i', $value) === 1) {
            return true;
        }

        if (in_array($name, ['href', 'src', 'poster'], true)) {
            if (str_starts_with($value, '#') || (str_starts_with($value, '/') && ! str_starts_with($value, '//'))) {
                return false;
            }

            if (preg_match('/^\s*(javascript|vbscript|data):/i', $value) === 1) {
                // Allow data:image for pasted inline images.
                if ($name === 'src' && preg_match('/^\s*data:image\/(png|jpe?g|gif|webp|svg\+xml);base64,/i', $value) === 1) {
                    return false;
                }

                return true;
            }

            return preg_match('/^(https?:\/\/|mailto:|tel:)/i', $value) !== 1
                && ! str_starts_with($value, './')
                && ! str_starts_with($value, '../');
        }

        return false;
    }

    protected static function allowedIframeSrc(string $src): bool
    {
        $src = trim($src);
        if ($src === '') {
            return false;
        }

        return (bool) preg_match(
            '#^https://(www\.)?(youtube\.com|youtube-nocookie\.com|player\.vimeo\.com|maps\.google\.com|www\.google\.com/maps)/#i',
            $src,
        );
    }

    protected static function safeStyle(string $style): string
    {
        $allowedProperties = [
            'width', 'height', 'max-width', 'max-height', 'min-width', 'min-height',
            'margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left',
            'padding', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left',
            'color', 'background', 'background-color', 'background-image', 'background-size', 'background-position', 'background-repeat',
            'text-align', 'text-decoration', 'text-transform', 'font-size', 'font-weight', 'font-style', 'line-height', 'letter-spacing',
            'display', 'flex', 'flex-direction', 'flex-wrap', 'justify-content', 'align-items', 'gap', 'row-gap', 'column-gap',
            'border', 'border-top', 'border-right', 'border-bottom', 'border-left', 'border-radius', 'border-color', 'border-width', 'border-style',
            'float', 'clear', 'overflow', 'opacity', 'vertical-align', 'list-style', 'list-style-type', 'white-space', 'object-fit',
        ];

        $safe = [];
        foreach (explode(';', $style) as $rule) {
            if (! str_contains($rule, ':')) {
                continue;
            }
            [$property, $value] = array_pad(explode(':', $rule, 2), 2, null);
            $property = strtolower(trim((string) $property));
            $value = trim((string) $value);
            if ($value === '' || ! in_array($property, $allowedProperties, true)) {
                continue;
            }
            if (preg_match('/expression\s*\(|javascript:|url\s*\(\s*[\'"]?\s*javascript:/i', $value) === 1) {
                continue;
            }
            // Allow common CSS values; block unbalanced quotes that could break out.
            if (substr_count($value, '"') % 2 === 1 || substr_count($value, "'") % 2 === 1) {
                continue;
            }
            $safe[] = $property.': '.$value;
        }

        return implode('; ', $safe);
    }

    protected static function innerHtml(DOMDocument $document, DOMElement $element): string
    {
        $html = '';
        foreach ($element->childNodes as $child) {
            $html .= $document->saveHTML($child);
        }

        return $html;
    }

    protected static function textContent(?DOMNode $node): string
    {
        if ($node === null) {
            return '';
        }

        return trim(preg_replace('/\s+/u', ' ', $node->textContent ?? '') ?? '');
    }

    protected static function metaContent(DOMDocument $document, string $name): string
    {
        foreach ($document->getElementsByTagName('meta') as $meta) {
            if (! $meta instanceof DOMElement) {
                continue;
            }
            $key = strtolower($meta->getAttribute('name') ?: $meta->getAttribute('property'));
            if ($key === strtolower($name)) {
                return trim($meta->getAttribute('content'));
            }
        }

        return '';
    }

    protected static function firstHeading(string $html): string
    {
        if (preg_match('/<h[1-3][^>]*>(.*?)<\/h[1-3]>/is', $html, $match) !== 1) {
            return '';
        }

        return trim(html_entity_decode(strip_tags($match[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}
