<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailsImport extends Model
{
    protected $table = 'emails_import';
    protected $fillable = [
        'email',
        'username',
        'line_bot_account_id'
    ];
}
