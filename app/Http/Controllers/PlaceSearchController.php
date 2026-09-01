<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class PlaceSearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $needle = mb_strtolower($query);
        $local = collect(config('qibla.cities', []))
            ->filter(fn (array $city): bool => str_contains(mb_strtolower($city['name'].' '.$city['country']), $needle))
            ->take(6)
            ->map(fn (array $city): array => $this->formatPlace(
                $city['name'],
                $city['country'],
                (float) $city['lat'],
                (float) $city['lng'],
            ))
            ->values();

        $remote = Cache::remember(
            'places:v1:'.md5($needle.'|'.app()->getLocale()),
            now()->addHours(12),
            fn (): array => $this->searchNominatim($query),
        );

        $places = $local
            ->concat($remote)
            ->unique(fn (array $place): string => round($place['lat'], 3).':'.round($place['lng'], 3))
            ->take(10)
            ->values();

        return response()->json($places);
    }

    /**
     * @return list<array{name: string, country: string, label: string, lat: float, lng: float}>
     */
    private function searchNominatim(string $query): array
    {
        try {
            $response = Http::timeout(8)
                ->withHeaders([
                    'User-Agent' => 'QiblaFinder/1.0 ('.config('app.url').')',
                    'Accept-Language' => str_replace('_', '-', app()->getLocale()),
                ])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $query,
                    'format' => 'jsonv2',
                    'addressdetails' => 1,
                    'limit' => 8,
                ]);
        } catch (Throwable) {
            return [];
        }

        if (! $response->successful()) {
            return [];
        }

        return collect($response->json() ?: [])
            ->map(function (array $row): ?array {
                $address = $row['address'] ?? [];
                $name = $address['city']
                    ?? $address['town']
                    ?? $address['village']
                    ?? $address['municipality']
                    ?? $address['suburb']
                    ?? $row['name']
                    ?? explode(',', (string) ($row['display_name'] ?? ''))[0]
                    ?? null;

                if (! is_string($name) || trim($name) === '') {
                    return null;
                }

                return $this->formatPlace(
                    trim($name),
                    (string) ($address['country'] ?? ''),
                    (float) $row['lat'],
                    (float) $row['lon'],
                );
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array{name: string, country: string, label: string, lat: float, lng: float}
     */
    private function formatPlace(string $name, string $country, float $lat, float $lng): array
    {
        return [
            'name' => $name,
            'country' => $country,
            'label' => $country !== '' ? "{$name}, {$country}" : $name,
            'lat' => $lat,
            'lng' => $lng,
        ];
    }
}
