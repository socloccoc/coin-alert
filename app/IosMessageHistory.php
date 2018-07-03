<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IosMessageHistory extends Model
{
    protected $table = 'ios_message_history';

    protected $fillable = [
        'user_id',
        'bot_id',
        'message_content',
        'time_send'
    ];
}
