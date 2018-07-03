<?php

return [
    /*
    |--------------------------------------------------------------------------
    | excepted line bot id
    |--------------------------------------------------------------------------
    |
    | Add line bot id that you don't want to send signal
    |
    */

    'EXCEPTED_LINE_BOTS' => [
        getenv('DEBUG_BOT_ID')
    ]
];
