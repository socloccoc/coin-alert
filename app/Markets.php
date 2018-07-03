<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Markets extends Model
{
    protected $table = 'markets';
    protected $fillable = [
        'name'
    ];
}
