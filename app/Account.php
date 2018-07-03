<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $table = 'account';
    //
    public const rules = ["name" => 'required|email'];
}
