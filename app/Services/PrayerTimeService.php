<?php

namespace App\Services;

use DateTimeImmutable;
use DateTimeZone;

/**
 * Astronomical prayer time calculator based on the widely used PrayTimes.org method.
 */
class PrayerTimeService
{
    public function times(
        float $latitude,
        float $longitude,
        ?string $timezone = null,
        string $method = 'MWL',
        string $asr = 'Standard',
        ?DateTimeImmutable $date = null,
    ): array {
        $timezone ??= $this->guessTimezone($longitude);
        $tz = new DateTimeZone($timezone);
        $date ??= new DateTimeImmutable('now', $tz);
        $date = $date->setTimezone($tz)->setTime(0, 0);

        $params = config('qibla.calculation_methods.'.$method, config('qibla.calculation_methods.MWL'));
        $jDate = $this->julian($date) - $longitude / (15 * 24);
        $eqt = $this->sunPosition($jDate)['equation'];
        $decl = $this->sunPosition($jDate)['declination'];

        $dhuhrHours = 12 + (-$longitude / 15) - $eqt;
        $sunrise = $this->sunAngleTime($latitude, $decl, $eqt, $longitude, 0.833, $dhuhrHours, true);
        $sunset = $this->sunAngleTime($latitude, $decl, $eqt, $longitude, 0.833, $dhuhrHours, false);
        $fajr = $this->sunAngleTime($latitude, $decl, $eqt, $longitude, (float) $params['fajr'], $dhuhrHours, true);

        if ($method === 'Makkah') {
            $isha = $sunset + 90 / 60;
        } else {
            $isha = $this->sunAngleTime($latitude, $decl, $eqt, $longitude, (float) $params['isha'], $dhuhrHours, false);
        }

        $asrShadow = $asr === 'Hanafi' ? 2 : 1;
        $asr = $this->asrTime($latitude, $decl, $eqt, $longitude, $asrShadow, $dhuhrHours);
        $maghrib = $sunset;
        $midnight = $this->normalizeHours($sunset + $this->timeDiff($sunset, $sunrise) / 2);
        $imsak = $fajr - 10 / 60;

        $offsetHours = $date->getOffset() / 3600;

        $map = [
            'imsak' => $imsak,
            'fajr' => $fajr,
            'sunrise' => $sunrise,
            'dhuhr' => $dhuhrHours,
            'asr' => $asr,
            'maghrib' => $maghrib,
            'isha' => $isha,
            'midnight' => $midnight,
        ];

        $formatted = [];
        foreach ($map as $name => $hours) {
            $formatted[$name] = $this->formatTime($hours + $offsetHours, $date);
        }

        $next = $this->nextPrayer($formatted, $date);

        return [
            'date' => $date->format('Y-m-d'),
            'hijri' => $this->hijri($date),
            'timezone' => $timezone,
            'method' => $method,
            'times' => $formatted,
            'next' => $next,
        ];
    }

    public function month(
        float $latitude,
        float $longitude,
        int $year,
        int $month,
        string $timezone = 'UTC',
        string $method = 'MWL',
        string $asr = 'Standard',
    ): array {
        $tz = new DateTimeZone($timezone);
        $start = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month), $tz);
        $days = (int) $start->format('t');
        $rows = [];

        for ($day = 1; $day <= $days; $day++) {
            $date = $start->setDate($year, $month, $day);
            $rows[] = $this->times($latitude, $longitude, $timezone, $method, $asr, $date);
        }

        return $rows;
    }

    protected function nextPrayer(array $times, DateTimeImmutable $dayStart): array
    {
        $now = new DateTimeImmutable('now', $dayStart->getTimezone());
        $sequence = ['fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha'];

        foreach ($sequence as $name) {
            $at = DateTimeImmutable::createFromFormat('Y-m-d H:i', $dayStart->format('Y-m-d').' '.$times[$name], $dayStart->getTimezone());
            if ($at && $at > $now) {
                return [
                    'name' => $name,
                    'time' => $times[$name],
                    'in_seconds' => $at->getTimestamp() - $now->getTimestamp(),
                ];
            }
        }

        $tomorrowFajr = DateTimeImmutable::createFromFormat(
            'Y-m-d H:i',
            $dayStart->modify('+1 day')->format('Y-m-d').' '.$times['fajr'],
            $dayStart->getTimezone(),
        );

        return [
            'name' => 'fajr',
            'time' => $times['fajr'],
            'in_seconds' => $tomorrowFajr ? $tomorrowFajr->getTimestamp() - $now->getTimestamp() : 0,
            'tomorrow' => true,
        ];
    }

    protected function hijri(DateTimeImmutable $date): string
    {
        if (! class_exists(\IntlDateFormatter::class)) {
            return '';
        }

        $formatter = new \IntlDateFormatter(
            'en_US@calendar=islamic-civil',
            \IntlDateFormatter::LONG,
            \IntlDateFormatter::NONE,
            $date->getTimezone()->getName(),
            \IntlDateFormatter::TRADITIONAL,
            'd MMMM yyyy',
        );

        return $formatter->format($date) ?: '';
    }

    protected function julian(DateTimeImmutable $date): float
    {
        $y = (int) $date->format('Y');
        $m = (int) $date->format('n');
        $d = (int) $date->format('j');

        if ($m <= 2) {
            $y -= 1;
            $m += 12;
        }

        $a = floor($y / 100);
        $b = 2 - $a + floor($a / 4);

        return floor(365.25 * ($y + 4716)) + floor(30.6001 * ($m + 1)) + $d + $b - 1524.5;
    }

    protected function sunPosition(float $jd): array
    {
        $d = $jd - 2451545.0;
        $g = $this->fixAngle(357.529 + 0.98560028 * $d);
        $q = $this->fixAngle(280.459 + 0.98564736 * $d);
        $l = $this->fixAngle($q + 1.915 * sin(deg2rad($g)) + 0.020 * sin(deg2rad(2 * $g)));
        $e = 23.439 - 0.00000036 * $d;
        $ra = rad2deg(atan2(cos(deg2rad($e)) * sin(deg2rad($l)), cos(deg2rad($l)))) / 15;
        $ra = $this->normalizeHours($ra);
        $decl = rad2deg(asin(sin(deg2rad($e)) * sin(deg2rad($l))));
        $eqt = $q / 15 - $ra;

        return ['declination' => $decl, 'equation' => $eqt];
    }

    protected function sunAngleTime(
        float $lat,
        float $decl,
        float $eqt,
        float $lng,
        float $angle,
        float $dhuhr,
        bool $ccw,
    ): float {
        $term = -sin(deg2rad($angle)) - sin(deg2rad($lat)) * sin(deg2rad($decl));
        $denom = cos(deg2rad($lat)) * cos(deg2rad($decl));
        $ratio = $denom == 0.0 ? 1 : $term / $denom;
        $ratio = max(-1, min(1, $ratio));
        $t = rad2deg(acos($ratio)) / 15;

        return $this->normalizeHours($dhuhr + ($ccw ? -$t : $t));
    }

    protected function asrTime(float $lat, float $decl, float $eqt, float $lng, int $shadow, float $dhuhr): float
    {
        $angle = -rad2deg(atan(1 / ($shadow + tan(abs(deg2rad($lat) - deg2rad($decl))))));

        return $this->sunAngleTime($lat, $decl, $eqt, $lng, $angle, $dhuhr, false);
    }

    protected function formatTime(float $hours, DateTimeImmutable $date): string
    {
        $hours = $this->normalizeHours($hours + 0.5 / 60);
        $h = (int) floor($hours);
        $m = (int) floor(($hours - $h) * 60);

        return sprintf('%02d:%02d', $h % 24, $m);
    }

    protected function timeDiff(float $a, float $b): float
    {
        return $this->normalizeHours($b - $a);
    }

    protected function normalizeHours(float $hours): float
    {
        $hours = fmod($hours, 24);
        if ($hours < 0) {
            $hours += 24;
        }

        return $hours;
    }

    protected function fixAngle(float $angle): float
    {
        $angle = fmod($angle, 360);
        if ($angle < 0) {
            $angle += 360;
        }

        return $angle;
    }

    protected function guessTimezone(float $longitude): string
    {
        $offset = (int) round($longitude / 15);
        $sign = $offset >= 0 ? '+' : '-';

        return sprintf('Etc/GMT%s%d', $sign === '+' ? '-' : '+', abs($offset));
    }
}
