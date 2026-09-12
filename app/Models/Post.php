<?php

namespace App\Models;

use App\Models\Concerns\LocalizesContent;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['title', 'slug', 'locale', 'excerpt', 'content', 'cover_image', 'meta_title', 'meta_description', 'is_published', 'published_at'])]
class Post extends Model
{
    use LocalizesContent;

    protected static function localeIdentityColumn(): ?string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Post $post) {
            if (blank($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
            if ($post->is_published && blank($post->published_at)) {
                $post->published_at = now();
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(function (Builder $builder) {
                $builder->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }
}
