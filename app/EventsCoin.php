<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EventsCoin extends Model
{
    protected $table = 'events_coin';
    protected $fillable = [
        'event_id',
        'coin_name',
        'date_event',
        'content_event',
        'source_url',
        'sent'
    ];
}
