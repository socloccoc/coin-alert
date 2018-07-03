<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MailTemplate extends Model
{
    protected $table = 'mail_template';

    protected $fillable = [
        'title',
        'content',
        'type'
    ];
}
