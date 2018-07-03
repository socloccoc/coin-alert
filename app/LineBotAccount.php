<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LineBotAccount extends Model {
    protected $table = 'line_bot_account';
    protected $fillable = [
        'type',
        'linebot_channel_name',
        'linebot_channel_token',
        'linebot_channel_secret',
        'qr_code',
        'pair_id',
        'is_active',
    ];
}
