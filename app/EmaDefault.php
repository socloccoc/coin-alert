<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmaDefault extends Model
{
    protected $table = 'ema_default';
    protected $fillable = [
        'ema_default_1', 'ema_default_2'
    ];
}
