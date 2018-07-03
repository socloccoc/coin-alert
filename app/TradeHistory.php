<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TradeHistory extends Model
{
    protected $table = 'trade_history';
    protected $casts = ['buy_price' => 'float','sell_price' => 'float','profit' => 'float'];

    protected $fillable = [
        'market_id',
        'coin_name',
        'pair',
        'buy_price',
        'sell_price',
        'bought_at',
        'sold_at',
        'is_show',
        'line_bot_id'
    ];
    //

    public function getProfit()
    {
        return $this->sell_price - $this->buy_price;
    }
}
