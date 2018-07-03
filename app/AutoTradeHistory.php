<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AutoTradeHistory extends Model
{
    protected $table = 'auto_trade_histories';

    protected $fillable = [
        'coin_id',
        'user_id',
        'coin_name',
        'pair',
        'buy_order_id',
        'buy_price',
        'buy_time',
        'sell_order_id',
        'sell_price',
        'sell_time',
        'amount',
        'profit',
        'profit_amount',
        'status',
    ];
}
