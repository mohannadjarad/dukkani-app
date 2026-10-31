<?php

namespace App\Support;

class Money
{
    public static function format(int|float|null $fils, bool $currency = true, bool $decimals = true): string
    {
        $value = (int) round((float) $fils) / 1000;

        $formatted = $decimals
            ? number_format($value, 3, '.', ',')
            : number_format($value, 0, '.', ',');

        return $currency ? $formatted.' د.أ' : $formatted;
    }

    public static function jod(float|int|string $jod): int
    {
        return (int) round(((float) $jod) * 1000);
    }
}
