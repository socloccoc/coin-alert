<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IosBotAccount extends Model {
    protected $table = 'ios_bot_account';
    protected $fillable = [
        'ios_event_channel',
    ];
}
