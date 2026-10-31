<?php

namespace App\Support;

class Phone
{
    /**
     * Normalize a phone number into the digits-only international form used by
     * wa.me links. Local numbers that start with a trunk zero (for example
     * 0791111001) are converted using the given country code.
     */
    public static function normalize(?string $number, string $countryCode = '962'): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $number);

        if ($digits === null || $digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '0')) {
            $digits = $countryCode.substr($digits, 1);
        }

        return $digits;
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
