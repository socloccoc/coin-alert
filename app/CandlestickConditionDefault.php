<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CandlestickConditionDefault extends Model
{
    protected $table = 'candlestick_condition_default';
    protected $casts = [
        'condition_buy_default_1' => 'integer',
        'condition_sell_default_1' => 'integer',
        'condition_buy_default_2' => 'integer',
        'condition_sell_default_2' => 'integer'
    ];
    protected $fillable = [
        'market_id',
        'condition_buy_default_1',
        'condition_sell_default_1',
        'condition_buy_default_2',
        'condition_sell_default_2',
        'line_bot_id'
    ];
}