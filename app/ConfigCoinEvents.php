<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConfigCoinEvents extends Model
{
    protected $table = 'config_coin_events';
    protected $fillable = [
        'coin_name',
        'is_active'
    ];
}
