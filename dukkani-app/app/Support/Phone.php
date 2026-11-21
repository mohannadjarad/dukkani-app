<?php

namespace App\Support;

class Phone
{
    /**
     * Normalize a phone number into the digits-only international form used by
     * wa.me links. Local numbers that start with a trunk zero (for example
     * 0791111001) are converted using the given country code. Numbers that are
     * already international are kept as they are, so this is safe to run on a
     * stored value whose country may differ from the store's.
     */
    public static function normalize(?string $number, string $countryCode = '962'): ?string
    {
        $digits = self::digits($number);

        if ($digits === null) {
            return null;
        }

        // Already international (201012345678): keep the country code that is
        // already there instead of prefixing a second one.
        if (str_starts_with($digits, $countryCode) && strlen($digits) > strlen($countryCode)) {
            return $digits;
        }

        // Local number with a trunk zero (059..., 010...) becomes international.
        if (str_starts_with($digits, '0')) {
            return $countryCode.substr($digits, 1);
        }

        return $digits;
    }

    /**
     * Complete a local number the customer typed into international form using
     * the country she picked. Unlike normalize(), a number without a trunk zero
     * is treated as local and the country code is added, because the phone field
     * always carries the country separately.
     */
    public static function internationalize(?string $number, string $countryCode = '962'): ?string
    {
        $digits = self::digits($number);

        if ($digits === null) {
            return null;
        }

        // The customer typed the country code herself (+20 10... / 0020...).
        if (str_starts_with($digits, $countryCode) && strlen($digits) > strlen($countryCode)) {
            return $digits;
        }

        // A local number with a trunk zero (059..., 010...).
        if (str_starts_with($digits, '0')) {
            return $countryCode.substr($digits, 1);
        }

        // A bare national number (791111001, 1012345678).
        return $countryCode.$digits;
    }

    /** Digits only, with a leading international 00 prefix stripped. */
    private static function digits(?string $number): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $number);

        if ($digits === null || $digits === '') {
            return null;
        }

        return str_starts_with($digits, '00') ? substr($digits, 2) : $digits;
    }

    /**
     * Build a wa.me link for a number and optional pre-filled text.
     */
    public static function whatsappLink(?string $number, ?string $text = null, string $countryCode = '962'): ?string
    {
        $normalized = self::normalize($number, $countryCode);

        if ($normalized === null) {
            return null;
        }

        return 'https://wa.me/'.$normalized.($text ? '?text='.rawurlencode($text) : '');
    }
}
