<?php

namespace App\Support;

class Money
{
    /** Currency used by format() when no explicit code is given. */
    protected static ?string $currency = null;

    /**
     * Set the active currency for the current request. Called by the store
     * middleware from the resolved store; passing null restores the default.
     */
    public static function useCurrency(?string $code): void
    {
        self::$currency = $code ? strtoupper($code) : null;
    }

    public static function currentCurrency(): string
    {
        return strtoupper(self::$currency ?? (string) config('app.currency', 'JOD'));
    }

    public static function symbol(?string $code = null): string
    {
        return match (strtoupper($code ?? self::currentCurrency())) {
            'ILS' => '₪',
            'SAR' => 'ر.س',
            'AED' => 'د.إ',
            'EGP' => 'ج.م',
            'QAR' => 'ر.ق',
            'KWD' => 'د.ك',
            'BHD' => 'د.ب',
            'OMR' => 'ر.ع',
            'SYP' => 'ل.س',
            'LBP' => 'ل.ل',
            'USD' => '$',
            'EUR' => '€',
            default => 'د.أ',
        };
    }

    public static function decimals(?string $code = null): int
    {
        return in_array(strtoupper($code ?? self::currentCurrency()), ['JOD', 'KWD', 'BHD', 'OMR'], true) ? 3 : 2;
    }

    /**
     * Format a value stored in thousandths of the major unit. JOD shows three
     * decimals, ILS two; the stored unit never changes.
     */
    public static function format(int|float|null $fils, bool $currency = true, bool $decimals = true, ?string $code = null): string
    {
        $code ??= self::currentCurrency();
        $value = (int) round((float) $fils) / 1000;

        $formatted = $decimals
            ? number_format($value, self::decimals($code), '.', ',')
            : number_format($value, 0, '.', ',');

        return $currency ? $formatted.' '.self::symbol($code) : $formatted;
    }

    /**
     * Convert a major-unit amount in the store currency into stored thousandths.
     */
    public static function toFils(float|int|string $amount): int
    {
        return (int) round(((float) $amount) * 1000);
    }

    /** Backwards-compatible alias for toFils(). */
    public static function jod(float|int|string $jod): int
    {
        return self::toFils($jod);
    }
}
