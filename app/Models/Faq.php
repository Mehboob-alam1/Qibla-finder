<?php

namespace App\Models;

use App\Models\Concerns\LocalizesContent;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['question', 'answer', 'locale', 'category', 'is_published', 'sort_order'])]
class Faq extends Model
{
    use LocalizesContent;

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
