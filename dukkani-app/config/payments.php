<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment methods
    |--------------------------------------------------------------------------
    |
    | The label shown to the customer, a short label for dashboard filters,
    | a hint line, and the outline icon path. A store is only offered the
    | methods returned by Store::allowedPaymentMethods().
    |
    */

    'methods' => [
        'cash' => [
            'label' => 'نقداً عند الاستلام',
            'short' => 'نقداً',
            'hint' => 'تدفعين عند استلام الطلب أو وصوله.',
            'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z',
        ],
        'cliq' => [
            'label' => 'تحويل CliQ',
            'short' => 'CliQ',
            'hint' => 'تحويل فوري من تطبيق بنكك.',
            'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z',
        ],
        'bank_transfer' => [
            'label' => 'حوالة بنكية',
            'short' => 'حوالة بنكية',
            'hint' => 'تحويل بنكي، وتُرسل صورة الإشعار على واتساب.',
            'icon' => 'M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | CliQ countries
    |--------------------------------------------------------------------------
    |
    | CliQ is a Jordanian instant-transfer service, so it is only offered to
    | stores whose country dial code appears here.
    |
    */

    'cliq_countries' => ['962'],

];
