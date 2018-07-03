<?php

namespace App\Config;

class SysConfig
{
    public static $pairMarket = [
        [ 'id' => 1, "name" =>'BTC'],
        [ 'id' => 2, 'name' => 'ETH'],
        [ 'id' => 3, 'name' => 'XMR'],
        [ 'id' => 4 , 'name'=> 'USDT'],
        [ 'id' => 5 , 'name'=> 'BNB'],
        [ 'id' => 6 , 'name'=> 'JPY']
    ];

    public static $pairMarketJson = [
        "1" =>'BTC',
        '2' => 'ETH',
        '3' => 'XMR',
        '4' => 'USDT',
        '5' => 'BNB',
        '6' => 'JPY'

    ];

    public static $typeMessage = [
        ['id' => 1, 'name' => 'Buy'],
         ['id' => 2, 'name' => 'Sell']
    ];
}
