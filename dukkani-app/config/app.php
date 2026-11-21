<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Demo Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, the one-click demo login is available and the interface
    | shows a clear "demo store" label. Disable this before a real launch.
    |
    */

    'demo_mode' => (bool) env('DEMO_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Default Store Slug
    |--------------------------------------------------------------------------
    |
    | In production the root URL redirects to this store instead of picking
    | the first active store. Leave empty to keep the "first active store"
    | behaviour (useful for local development).
    |
    */

    'default_store_slug' => env('DEFAULT_STORE_SLUG'),

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | Fallback currency used by Money::format() when no store is in context
    | (CLI commands, jobs, tests). Each store stores its own currency.
    |
    */

    'currency' => env('APP_CURRENCY', 'JOD'),

    /*
    |--------------------------------------------------------------------------
    | Country defaults
    |--------------------------------------------------------------------------
    |
    | Dial code => the local currency and IANA timezone used when a store is
    | created in that country. A store keeps its own currency/timezone columns,
    | so changing a store's country later does not silently rewrite them.
    |
    */

    'countries' => [
        '962' => ['name' => 'الأردن', 'currency' => 'JOD', 'timezone' => 'Asia/Amman', 'example' => '07 9111 1001'],
        '970' => ['name' => 'فلسطين', 'currency' => 'ILS', 'timezone' => 'Asia/Hebron', 'example' => '059 911 2233'],
        '972' => ['name' => 'فلسطين', 'currency' => 'ILS', 'timezone' => 'Asia/Hebron', 'example' => '059 911 2233'],
        '963' => ['name' => 'سوريا', 'currency' => 'SYP', 'timezone' => 'Asia/Damascus', 'example' => '09 1234 567'],
        '961' => ['name' => 'لبنان', 'currency' => 'LBP', 'timezone' => 'Asia/Beirut', 'example' => '03 123 456'],
        '20' => ['name' => 'مصر', 'currency' => 'EGP', 'timezone' => 'Africa/Cairo', 'example' => '10 1234 5678'],
        '966' => ['name' => 'السعودية', 'currency' => 'SAR', 'timezone' => 'Asia/Riyadh', 'example' => '05 1234 5678'],
        '971' => ['name' => 'الإمارات', 'currency' => 'AED', 'timezone' => 'Asia/Dubai', 'example' => '05 123 4567'],
        '974' => ['name' => 'قطر', 'currency' => 'QAR', 'timezone' => 'Asia/Qatar', 'example' => '3312 3456'],
        '973' => ['name' => 'البحرين', 'currency' => 'BHD', 'timezone' => 'Asia/Bahrain', 'example' => '3612 3456'],
        '965' => ['name' => 'الكويت', 'currency' => 'KWD', 'timezone' => 'Asia/Kuwait', 'example' => '5123 4567'],
        '968' => ['name' => 'عُمان', 'currency' => 'OMR', 'timezone' => 'Asia/Muscat', 'example' => '9123 4567'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */

    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Laravel's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
