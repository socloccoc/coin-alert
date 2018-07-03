<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConfigCoinBot extends Model {
    protected $table = 'config_coin_bot';
    protected $fillable = [
        'coin_id',
        'line_bot_id'
    ];

    public function configCoin()
    {
        return $this->belongsTo('App\ConfigCoin', 'coin_id');
    }
}
