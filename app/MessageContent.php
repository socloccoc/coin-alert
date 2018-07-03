<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MessageContent extends Model
{
    protected $table = 'message_content';
    
    protected $casts = [ 'content_type' => 'integer' ];
    protected $fillable = [
            'content_type','content'
        ];
    //
}
