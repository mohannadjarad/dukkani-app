<?php

namespace App\Support;

class Country
{
    /**
     * Dial codes the app supports, in the order customers should see them.
     * The store's own country is moved to the top so most customers never
     * have to touch the selector.
     *
     * @return array<string, string> dial code => "name +dial"
     */
    public static function options(?string $preferred = null): array
    {
        $options = [];

        foreach ((array) config('app.countries', []) as $dial => $country) {
            $dial = (string) $dial;
            $name = (string) ($country['name'] ?? $dial);
            $options[$dial] = $name.' +'.$dial;
        }

        if ($preferred !== null && isset($options[$preferred])) {
            $options = [$preferred => $options[$preferred]] + $options;
        }

        return $options;
    }

    /**
     * A local-format mobile number customers in this country will recognise,
     * used as the phone field's example so a store in Egypt does not show a
     * Jordanian-looking number.
     */
    public static function example(?string $dial): string
    {
        return (string) (self::config($dial)['example'] ?? '');
    }

    /** @return array<string, string> dial code => local example number */
    public static function examples(): array
    {
        $examples = [];

        foreach ((array) config('app.countries', []) as $dial => $country) {
            $examples[(string) $dial] = (string) ($country['example'] ?? '');
        }

        return $examples;
    }

    public static function name(?string $dial): ?string
    {
        return self::config($dial)['name'] ?? null;
    }

    public static function isSupported(?string $dial): bool
    {
        return $dial !== null && array_key_exists($dial, (array) config('app.countries', []));
    }

    /** @return array<string, mixed> */
    private static function config(?string $dial): array
    {
        if ($dial === null) {
            return [];
        }

        return (array) config('app.countries.'.$dial, []);
    }
}
