<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait LocalizesContent
{
    /**
     * Only rows for the active locale — English CMS stays for English; no cross-language fallback.
     */
    public function scopeForLocale(Builder $query, ?string $locale = null): Builder
    {
        return $query->where('locale', $locale ?: app()->getLocale());
    }
}
