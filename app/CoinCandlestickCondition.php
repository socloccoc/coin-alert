<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CoinCandlestickCondition extends Model
{
    protected $table = 'coin_candlestick_condition';
    protected $casts = [
        'condition_buy_1' => 'integer',
        'condition_sell_1' => 'integer',
        'condition_buy_2' => 'integer',
        'condition_sell_2' => 'integer',

    ];
    protected $fillable = [
        'coin_id',
        'condition_buy_1',
        'condition_sell_1',
        'condition_buy_2',
        'condition_sell_2',
        'current_trend_type',
        'line_bot_id'
    ];

    /**
     * Relationship belongsTo table config_coin with table coin_candlestick_condition
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function coinConfig()
    {
        return $this->belongsTo(ConfigCoin::class,  'coin_id', 'id');
    }
}
