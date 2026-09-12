<?php

namespace App\Models;

use App\Models\Concerns\LocalizesContent;
use App\Support\LocalizedPaths;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['title', 'slug', 'url_style', 'locale', 'content', 'meta_title', 'meta_description', 'is_published', 'show_in_header', 'show_in_footer', 'sort_order'])]
class Page extends Model
{
    use LocalizesContent;

    /**
     * Paths that already belong to the app and cannot be used as flat CMS URLs.
     *
     * @return list<string>
     */
    public static function reservedSlugs(): array
    {
        return [
            'admin',
            'cities',
            'contact',
            'faq',
            'guides',
            ...LocalizedPaths::reservedSlugs(),
            'locale',
            'offline',
            'p',
            'places',
            'prayer-times',
            'qibla',
            'qibla.json',
            'robots.txt',
            'sitemap.xml',
            'up',
        ];
    }

    public function isFlat(): bool
    {
        return ($this->url_style ?: 'prefixed') === 'flat';
    }

    public function publicPath(): string
    {
        return $this->isFlat() ? '/'.$this->slug : '/p/'.$this->slug;
    }

    public function publicUrl(): string
    {
        return url($this->publicPath());
    }

    public function isCurrent(): bool
    {
        return '/'.ltrim(request()->path(), '/') === $this->publicPath();
    }

    public function navLabel(): string
    {
        return match ($this->slug) {
            'duas-qibla' => __('ui.Dua'),
            'about' => __('ui.About'),
            'privacy' => __('ui.Privacy Policy'),
            'terms' => __('ui.Terms of Service'),
            default => $this->title,
        };
    }

    protected static function localeIdentityColumn(): ?string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'show_in_header' => 'boolean',
            'show_in_footer' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Page $page) {
            if (blank($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeInHeader(Builder $query): Builder
    {
        return $query->where('show_in_header', true);
    }

    public function scopeInFooter(Builder $query): Builder
    {
        return $query->where('show_in_footer', true);
    }
}
