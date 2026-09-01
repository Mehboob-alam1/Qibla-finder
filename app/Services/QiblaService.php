<?php

namespace App\Services;

class QiblaService
{
    public function __construct(
        public readonly float $kaabaLat = 21.422487,
        public readonly float $kaabaLng = 39.826206,
    ) {}

    /**
     * Compass bearing from a location to the Kaaba, in degrees (0–360, clockwise from true north).
     */
    public function bearing(float $latitude, float $longitude): float
    {
        $lat1 = deg2rad($latitude);
        $lng1 = deg2rad($longitude);
        $lat2 = deg2rad($this->kaabaLat);
        $lng2 = deg2rad($this->kaabaLng);
        $dLng = $lng2 - $lng1;

        $y = sin($dLng);
        $x = cos($lat1) * tan($lat2) - sin($lat1) * cos($dLng);
        $bearing = rad2deg(atan2($y, $x));

        return fmod($bearing + 360.0, 360.0);
    }

    /**
     * Great-circle distance to the Kaaba in kilometres.
     */
    public function distanceKm(float $latitude, float $longitude): float
    {
        $earthRadius = 6371.0088;
        $dLat = deg2rad($this->kaabaLat - $latitude);
        $dLng = deg2rad($this->kaabaLng - $longitude);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($latitude)) * cos(deg2rad($this->kaabaLat)) * sin($dLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function cardinal(float $bearing): string
    {
        $directions = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];
        $index = (int) round($bearing / 22.5) % 16;

        return $directions[$index];
    }

    public function snapshot(float $latitude, float $longitude): array
    {
        $bearing = $this->bearing($latitude, $longitude);
        $km = $this->distanceKm($latitude, $longitude);

        return [
            'latitude' => round($latitude, 6),
            'longitude' => round($longitude, 6),
            'qibla_bearing' => round($bearing, 2),
            'qibla_cardinal' => $this->cardinal($bearing),
            'distance_km' => round($km, 1),
            'distance_mi' => round($km * 0.621371, 1),
            'kaaba' => [
                'latitude' => $this->kaabaLat,
                'longitude' => $this->kaabaLng,
            ],
        ];
    }
}
