<?php

return [
    /*
     * Web Push (VAPID) credentials. Generate a pair once and keep it in the
     * environment; rotating the keys invalidates every saved subscription.
     */
    'public_key' => env('VAPID_PUBLIC_KEY'),
    'private_key' => env('VAPID_PRIVATE_KEY'),
    'subject' => env('VAPID_SUBJECT', 'https://dukkani.app'),
];
