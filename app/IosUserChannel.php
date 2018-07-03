<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IosUserChannel extends Model
{
    protected $table = 'ios_user_channel';

    protected $fillable = [
        'user_id',
        'bot_id',
        'is_subscribe',
        'enable_ios',
        'is_request_active',
    ];
}
