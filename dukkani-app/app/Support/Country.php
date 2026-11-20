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

    public static function isSupported(?string $dial): bool
    {
        return $dial !== null && array_key_exists($dial, (array) config('app.countries', []));
    }
}
