<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserExceptCoin extends Model
{
    protected $table = 'user_except_coin';

    protected $casts = [ 'account_id' => 'integer', 'coin_id' => 'integer', 'line_bot_id' => 'integer' ];
    protected $fillable = [
            'account_id',
            'coin_id',
            'line_bot_id',
        ];
}
