<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LineUser extends Model
{
    protected $table = 'line_users';

    protected $fillable = [
        'account_id',
        'user_id',
        'line_bot_id',
        'display_name',
        'block'
    ];
}
