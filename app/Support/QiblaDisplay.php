<?php

namespace App\Support;

class QiblaDisplay
{
    /**
     * @return list<string>
     */
    public static function modes(): array
    {
        return ['compass', 'arrow', 'camera'];
    }

    public static function mode(?string $value): string
    {
        return in_array($value, self::modes(), true) ? $value : 'compass';
    }
}
