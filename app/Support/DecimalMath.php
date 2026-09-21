<?php

namespace App\Support;

/**
 * Unified money/quantity math: max 3 digits after the decimal point.
 */
class DecimalMath
{
    public const SCALE = 3;

    public static function round(mixed $value): float
    {
        return round((float) $value, self::SCALE);
    }

    public static function mul(mixed $a, mixed $b): float
    {
        return self::round((float) $a * (float) $b);
    }

    public static function div(mixed $a, mixed $b): float
    {
        $divisor = (float) $b;
        if (abs($divisor) < 1e-12) {
            return 0.0;
        }

        return self::round((float) $a / $divisor);
    }

    public static function add(mixed $a, mixed $b): float
    {
        return self::round((float) $a + (float) $b);
    }

    public static function sub(mixed $a, mixed $b): float
    {
        return self::round((float) $a - (float) $b);
    }

    public static function format(mixed $value, ?int $decimals = null): string
    {
        $decimals ??= self::SCALE;
        $n = self::round($value);

        return number_format($n, $decimals, '.', '');
    }

    public static function display(mixed $value, int $maxDecimals = self::SCALE): string
    {
        $n = self::round($value);
        $formatted = number_format($n, $maxDecimals, '.', '');
        $trimmed = rtrim(rtrim($formatted, '0'), '.');

        return $trimmed === '' ? '0' : $trimmed;
    }
}
