<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait LocalizesContent
{
    public function scopeForLocale(Builder $query, ?string $locale = null): Builder
    {
        $locale = $locale ?: app()->getLocale();
        $fallback = config('app.fallback_locale', 'en');

        if ($locale === $fallback) {
            return $query->where('locale', $fallback);
        }

        $column = static::localeIdentityColumn();

        if ($column === null) {
            $hasLocale = (clone $query)->where('locale', $locale)->exists();

            return $query->where('locale', $hasLocale ? $locale : $fallback);
        }

        $table = $query->getModel()->getTable();

        return $query->where(function (Builder $outer) use ($locale, $fallback, $column, $table): void {
            $outer->where('locale', $locale)
                ->orWhere(function (Builder $inner) use ($locale, $fallback, $column, $table): void {
                    $inner->where('locale', $fallback)
                        ->whereNotIn($column, function ($sub) use ($locale, $column, $table): void {
                            $sub->select($column)
                                ->from($table)
                                ->where('locale', $locale)
                                ->whereNotNull($column);
                        });
                });
        });
    }

    protected static function localeIdentityColumn(): ?string
    {
        return null;
    }
}
