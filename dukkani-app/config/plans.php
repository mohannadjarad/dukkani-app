<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Subscription plans
    |--------------------------------------------------------------------------
    |
    | Editable without touching code. A limit of null means "unlimited".
    | Prices are stored per currency so the upgrade screens can show a local
    | figure without a live exchange rate. `features` are boolean switches used
    | by the gates and the "coming soon" badges. The EGP figure is the JOD
    | price converted (1 JOD ≈ 75.8 EGP, Nov 2026).
    |
    */

    'default' => 'free',

    // A brand-new store gets a free full-feature Starter trial for this many
    // days, then falls back to the Free limits automatically.
    'trial_days' => 3,

    // The first time a store activates a paid plan, this many extra free days
    // are added on top of the paid period (a one-off first-month thank-you).
    // Granted only once per store, tracked by `stores.first_paid_at`.
    'first_month_bonus_days' => 7,

    // A paid plan keeps working for this many days after it expires before it
    // drops back to the Free limits (gives the owner time to renew).
    'grace_days' => 7,

    // Warn the owner when the remaining orders for the month fall to this
    // number or fewer.
    'warning_remaining' => 3,

    /*
    |--------------------------------------------------------------------------
    | Platform support contact
    |--------------------------------------------------------------------------
    |
    | Shown on the "store is full" storefront page and on the owner upgrade
    | prompts. Set DUKKANI_SUPPORT_WHATSAPP on Render to a real number.
    |
    */

    'support' => [
        'name' => env('DUKKANI_SUPPORT_NAME', 'دعم دكّاني'),
        'whatsapp' => env('DUKKANI_SUPPORT_WHATSAPP', '962790000000'),
    ],

    'plans' => [

        'free' => [
            'label' => 'المجانية',
            'label_latin' => 'Free',
            'price' => ['JOD' => 0, 'ILS' => 0, 'EGP' => 0],
            'order_limit' => 15,
            'product_limit' => 12,
            'user_limit' => 1,
            'purchasable' => true,
            'features' => [
                'customers' => false,
                'analytics' => false,
                'whatsapp_templates' => true,
            ],
            'coming_soon' => [],
        ],

        'starter' => [
            'label' => 'Starter',
            'label_latin' => 'Starter',
            'price' => ['JOD' => 29, 'ILS' => 131, 'EGP' => 2200],
            'order_limit' => null,
            'product_limit' => 12,
            'user_limit' => 1,
            'purchasable' => true,
            'features' => [
                'customers' => true,
                'analytics' => true,
                'whatsapp_templates' => true,
            ],
            'coming_soon' => [],
        ],

        // Pro is on the roadmap only. It is not for sale at launch: every extra
        // feature over Starter is still being built, so it stays "قريباً".
        'pro' => [
            'label' => 'Pro',
            'label_latin' => 'Pro',
            'price' => ['JOD' => 59, 'ILS' => 265, 'EGP' => 4470],
            'order_limit' => null,
            'product_limit' => null,
            'user_limit' => 3,
            'purchasable' => false,
            'features' => [
                'customers' => true,
                'analytics' => true,
                'whatsapp_templates' => true,
            ],
            'coming_soon' => ['حتى 3 مستخدمين', 'كوبونات وخصومات', 'مناطق توصيل متعددة', 'تصدير Excel/CSV', 'دومين مخصص'],
        ],

    ],

];
