<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TwitterLinks extends Model
{
    protected $table = 'twitter_links';

    protected $fillable = [
        'url',
        'name',
        'screen_name',
        'is_stopped',
    ];
}
