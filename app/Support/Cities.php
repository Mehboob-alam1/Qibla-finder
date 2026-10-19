<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Cities
{
    /**
     * @return Collection<int, array{name: string, country: string, lat: float, lng: float, slug: string, is_indexable: bool}>
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
                'is_indexable' => (bool) ($city['is_indexable'] ?? true),
            ];
        })->values();
    }

    /**
     * Curated whitelist for indexable /qibla/{slug} pages.
     *
     * @return Collection<int, array{name: string, country: string, lat: float, lng: float, slug: string, is_indexable: bool}>
     */
    public static function indexable(): Collection
    {
        return static::all()->where('is_indexable', true)->values();
    }

    /**
     * @return array{name: string, country: string, lat: float, lng: float, slug: string, is_indexable: bool}|null
     */
    public static function find(string $slug): ?array
    {
        return static::all()->firstWhere('slug', $slug);
    }

    /**
     * @return Collection<string, Collection<int, array{name: string, country: string, lat: float, lng: float, slug: string, is_indexable: bool}>>
     */
    public static function groupedByCountry(): Collection
    {
        return static::indexable()->groupBy('country')->sortKeys();
    }

    /**
     * @return Collection<int, array{name: string, country: string, lat: float, lng: float, slug: string, is_indexable: bool}>
     */
    public static function inCountry(string $country, ?string $except = null): Collection
    {
        return static::indexable()
            ->where('country', $country)
            ->when($except, fn (Collection $cities) => $cities->where('slug', '!=', $except))
            ->values();
    }

    /**
     * @return Collection<int, array{name: string, country: string, lat: float, lng: float, slug: string, is_indexable: bool}>
     */
    public static function popular(?string $locale = null): Collection
    {
        $slugs = match ($locale ?: app()->getLocale()) {
            'id' => ['jakarta', 'surabaya', 'bandung', 'kuala-lumpur', 'makkah', 'singapore', 'dubai', 'istanbul', 'london', 'new-york'],
            'ms' => ['kuala-lumpur', 'singapore', 'jakarta', 'brunei', 'makkah', 'dubai', 'london', 'istanbul', 'new-york', 'cairo'],
            default => ['makkah', 'madinah', 'london', 'new-york', 'dubai', 'istanbul', 'jakarta', 'karachi', 'cairo', 'kuala-lumpur'],
        };

        return static::indexable()
            ->whereIn('slug', $slugs)
            ->sortBy(fn (array $city) => array_search($city['slug'], $slugs, true))
            ->values();
    }

    /**
     * Nearest curated (indexable) city for lat/lng, or null if none.
     *
     * @return array{name: string, country: string, lat: float, lng: float, slug: string, is_indexable: bool}|null
     */
    public static function nearestIndexable(float $lat, float $lng): ?array
    {
        $best = null;
        $bestKm = INF;

        foreach (static::indexable() as $city) {
            $km = static::haversineKm($lat, $lng, $city['lat'], $city['lng']);
            if ($km < $bestKm) {
                $bestKm = $km;
                $best = $city;
            }
        }

        return $best;
    }

    public static function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earth = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * $earth * asin(min(1, sqrt($a)));
    }
}
