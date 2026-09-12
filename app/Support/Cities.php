<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Cities
{
    /**
     * @return Collection<int, array{name: string, country: string, lat: float, lng: float, slug: string}>
     */
    public static function all(): Collection
    {
        $used = [];

        return collect(config('qibla.cities', []))->map(function (array $city) use (&$used) {
            $slug = Str::slug($city['name']);
            if (isset($used[$slug])) {
                $slug = Str::slug($city['name'].'-'.$city['country']);
            }
            $used[$slug] = true;

            return [
                'name' => $city['name'],
                'country' => $city['country'],
                'lat' => (float) $city['lat'],
                'lng' => (float) $city['lng'],
                'slug' => $slug,
            ];
        })->values();
    }

    /**
     * @return array{name: string, country: string, lat: float, lng: float, slug: string}|null
     */
    public static function find(string $slug): ?array
    {
        return static::all()->firstWhere('slug', $slug);
    }

    /**
     * @return Collection<string, Collection<int, array{name: string, country: string, lat: float, lng: float, slug: string}>>
     */
    public static function groupedByCountry(): Collection
    {
        return static::all()->groupBy('country')->sortKeys();
    }

    /**
     * @return Collection<int, array{name: string, country: string, lat: float, lng: float, slug: string}>
     */
    public static function inCountry(string $country, ?string $except = null): Collection
    {
        return static::all()
            ->where('country', $country)
            ->when($except, fn (Collection $cities) => $cities->where('slug', '!=', $except))
            ->values();
    }

    /**
     * @return Collection<int, array{name: string, country: string, lat: float, lng: float, slug: string}>
     */
    public static function popular(?string $locale = null): Collection
    {
        $slugs = match ($locale ?: app()->getLocale()) {
            'id' => ['jakarta', 'surabaya', 'bandung', 'kuala-lumpur', 'makkah', 'singapore', 'dubai', 'istanbul', 'london', 'new-york'],
            'ms' => ['kuala-lumpur', 'singapore', 'jakarta', 'brunei', 'makkah', 'dubai', 'london', 'istanbul', 'new-york', 'cairo'],
            default => ['makkah', 'madinah', 'london', 'new-york', 'dubai', 'istanbul', 'jakarta', 'karachi', 'cairo', 'kuala-lumpur'],
        };

        return static::all()->whereIn('slug', $slugs)->sortBy(fn (array $city) => array_search($city['slug'], $slugs, true))->values();
    }
}
