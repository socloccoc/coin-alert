<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],
    // return Unix time
    'NUMBER_CANDLESTICK' => 1000, //GET 1000 CANDLESTICK FROM NOW
    'POLONIEX_TIME_API' => [
        'END' => 9999999999 //Sat, 20 Nov 2286 17:46:39 GMT
    ],
    // key of fire base
    'key_fire_base_old_app' => env('KEY_FIRE_BASE_OLD_APP'),
    'key_fire_base_new_app' => env('KEY_FIRE_BASE_NEW_APP')

];
