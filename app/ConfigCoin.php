<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConfigCoin extends Model
{
    protected $table = 'config_coin';
    protected $casts = ['ema_period_1' => 'integer', 'ema_period_2' => 'integer', 'cryptocurrency' => 'integer'];

    protected $fillable = [
        'market_id',
        'coin_name',
        'is_active',
        'cryptocurrency',
        'ema_period_1',
        'ema_period_2'
    ];

    public function configCoin()
    {
        return $this->hasMany('App\ConfigCoinBot', 'coin_id');
    }

    /**
     * Relationship hasMany table coin_candlestick_condition table config_coin
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function coinConditions()
    {
        return $this->hasMany(CoinCandlestickCondition::class, 'coin_id', 'id');
    }

    /**
     * Relationship belongsTo table config_coin with table markets
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function nameMarket()
    {
        return $this->belongsTo(Markets::class, 'market_id', 'id');
    }
}
