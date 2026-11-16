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
    | by the gates and the "coming soon" badges.
    |
    */

    'default' => 'free',

    // A brand-new store gets a free Starter trial for this many days.
    'trial_days' => 7,

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
            'price' => ['JOD' => 0, 'ILS' => 0],
            'order_limit' => 15,
            'product_limit' => 12,
            'user_limit' => 1,
            'features' => [
                'customers' => false,
                'analytics' => false,
                'verified_badge' => false,
                'whatsapp_templates' => true,
            ],
            'coming_soon' => [],
        ],

        'starter' => [
            'label' => 'Starter',
            'label_latin' => 'Starter',
            'price' => ['JOD' => 29, 'ILS' => 131],
            'order_limit' => null,
            'product_limit' => null,
            'user_limit' => 1,
            'features' => [
                'customers' => true,
                'analytics' => true,
                'verified_badge' => true,
                'whatsapp_templates' => true,
            ],
            'coming_soon' => [],
        ],

        'pro' => [
            'label' => 'Pro',
            'label_latin' => 'Pro',
            'price' => ['JOD' => 59, 'ILS' => 265],
            'order_limit' => null,
            'product_limit' => null,
            'user_limit' => 3,
            'features' => [
                'customers' => true,
                'analytics' => true,
                'verified_badge' => true,
                'whatsapp_templates' => true,
            ],
            'coming_soon' => ['حتى 3 مستخدمين', 'كوبونات وخصومات', 'مناطق توصيل متعددة', 'تصدير Excel/CSV', 'دومين مخصص'],
        ],

    ],

];
