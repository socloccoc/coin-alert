<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TweetsHistory extends Model
{
    protected $table = 'tweets_history';

    protected $fillable = [
        'twitter_link_id',
        'tweet_id',
        'tweet',
    ];
}
