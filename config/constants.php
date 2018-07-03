<?php

return [
    'STATUS_COIN' => [
        'ACTIVE' => 1,
        'INACTIVE' => 0,
    ],
    'MESSAGE_TYPE' => [
        'BUY' => 1,
        'SELL' => 2,
        'BUY_MANY' => 3,
        'SELL_MANY' => 4
    ],
    'STATUS_ALERT_EVENTS' => [
        'SENT' => 1,
        'NOT_SENT' => 0,
        'NOT_SENT_UPDATE' => 2
    ],
    'STATUS_LINE_USER' => [
        'BLOCK' => 1,
        'UNBLOCK' => 0,
    ],
    'STATUS_TRADE_HISTORY' => [
        'SHOW' => 1,
        'NOT_SHOW' => 0
    ],
    'STATUS_LINE_BOT' => [
        'ACTIVE' => 1,
        'INACTIVE' => 0,
    ],
    'MARKET_ID' => [
        'POLONIEX' => 1,
        'BINANCE' => 2,
        'BITFLYER' => 3

    ],
    'SIGNAL_TYPE' => [
        'SELL_STRONG' => 'SELL_STRONG',
        'SELL' => 'SELL',
        'BUY_STRONG' => 'BUY_STRONG',
        'BUY' => 'BUY',
    ],
    'IS_REQUEST_ACTIVE' => [
        'ACTIVE' => 2,
        'REQUEST' => 1,
        'NOREQUEST' => 0
    ],
    'EXCEPTION_NO_REPORT' => [
        'The given data was invalid.',
        'Unauthenticated.'
    ],
    'DEBUG_BOT_ID' => getenv('DEBUG_BOT_ID'),
    'BITLION_BOT_ID' => getenv('BITLION_BOT_ID'),
    'TWITTER_LINE_BOT_ID' => getenv('TWITTER_LINE_BOT_ID'),
    'BOT_ID_EVENT' => 1,
    'SEND_NOW' => 1,
    'CONDITION_TYPE' => [
        'ONLY_CONDITION2' => 1,
        'BOTH_CONDITION1_AND_CONDITION2' => 2,
    ],
    'CANDLESTICK_TYPE' => [
        'BUY' => 'buy',
        'SELL' => 'sell',
        'COMMON' => 'common'
    ],
    'CROSS_POINT_TYPE' => [
        'BUY' => 'buy',
        'SELL' => 'sell'
    ],
    'TREND_CONDITION1' => [
        'UP_TREND' => 1,
        'DOWN_TREND' => -1
    ],
    'CRON_JOB_TYPE' => [
        'GET_TREND' => 1,
        'GET_SIGNAL' => 2
    ],
    'STATUS_SIGNAL' => [
        'SENT' => 1,
        'NOT_SENT' => 0
    ],
    'COIN_CANDLESTICK_CONDITIONS' => [
        'POLONIEX' => [
            'condition_1' => [86400, 14400, 0],
            'condition_2' => [14400, 7200, 1800, 900, 300],
            'default' => [
                'condition_buy_1' => 86400,
                'condition_sell_1' => 86400,
                'condition_buy_2' => 1800,
                'condition_sell_2' => 1800
            ]
        ],
        'BINANCE' => [
            'condition_1' => [86400, 43200, 21600, 14400, 7200, 3600, 0],
            'condition_2' => [14400, 7200, 3600, 1800, 900, 300],
            'default' => [
                'condition_buy_1' => 86400,
                'condition_sell_1' => 43200,
                'condition_buy_2' => 1800,
                'condition_sell_2' => 1800
            ]
        ],
        'BITFLYER' => [
            'condition_1' => [86400, 43200, 21600, 14400, 7200, 3600, 0],
            'condition_2' => [14400, 7200, 3600, 1800, 900, 300],
            'default' => [
                'condition_buy_1' => 86400,
                'condition_sell_1' => 43200,
                'condition_buy_2' => 1800,
                'condition_sell_2' => 1800
            ]
        ]
    ],
    'ROLE_TYPE' => [
        'WEB' => 1,
        'IOS' => 2
    ],
    "ROLE_ACL" => [
        'ADMIN' => "ACL_AD",
        'USER' => "ACL_U1",
    ],
    'TYPE' => [
        'USER_IOS' => 2,
    ],
    'EMA' => [10, 20, 30, 40, 50, 60, 70, 80, 90],
    'EMA_DEFAULT_1' => 30,
    'EMA_DEFAULT_2' => 50,
    'STATUS_TWITTER_LINK' => [
        'STOPPED' => 1,
        'ACTIVE' => 0
    ],
    'DEBUG_BOT_USER_ID' => getenv('DEBUG_BOT_USER_ID'),
    'LINE_BOT_TYPE' => [
        'CONFIG_COIN' => 1,
        'OTHER' => 2,
    ],
    'IS_ADMIN_APPROVED' => [
        'ACTIVE' => 1,
        'INACTIVE' => 0,
    ],
    'MAIL_CONFIG' => getenv('MAIL_USERNAME'),
    'MAIL_TEMPLATE' => [
        'ACTIVE' => 1,
        'INACTIVE' => 2,
    ],
    'BINANCE_AUTO_TRADE' => [
        'USER_AUTO_TRADE' => [
            'ACTIVE' => 1,
            'STOP' => 0
        ],
        'CHECK_AMOUNT' => [
            'ENOUGH' => 1,
            'NOT_ENOUGH' => 0
        ],
        'COIN_AUTO_TRADE' => [
            'ACTIVE' => 1,
            'STOP' => 0
        ]

    ]
];